<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Filter\AllowedChangeFilter;

use Magento\SemanticVersionChecker\Filter\AllowedChangeFilter\PhpCommentFormattingFilter;
use PHPUnit\Framework\TestCase;

class PhpCommentFormattingFilterTest extends TestCase
{
    const INPUT_DATA = 'input';
    const EXPECTED_DATA = 'expected';

    /**
     * Test PhpCommentFormattingFilter file change filter
     *
     * 1. Run filter over test before/after files
     * 2. Assert that only files that differ by expected changes are removed from the returned file lists and filtered
     *    changes remain if the file still doesn't match
     *
     * @param string $dataFolder
     * @param bool $expectsRemaining
     * @return void
     *
     * @dataProvider filterDataProvider
     */
    public function testFilter($dataFolder, $expectsRemaining)
    {
        list($beforeFileContents, $afterFileContents) = $this->getTestData($dataFolder, self::INPUT_DATA);
        if ($expectsRemaining) {
            list($expectedBefore, $expectedAfter) = $this->getTestData($dataFolder, self::EXPECTED_DATA);
        } else {
            $expectedBefore = [];
            $expectedAfter = [];
        }

        $filter = new PhpCommentFormattingFilter();
        $filter->filter($beforeFileContents, $afterFileContents);

        $this->assertEquals($expectedBefore, $beforeFileContents);
        $this->assertEquals($expectedAfter, $afterFileContents);
    }

    /**
     * Define the name and data file location for each variation of the test
     *
     * @return array
     */
    public function filterDataProvider()
    {
        return [
            ['condensed_comments_match', false],
            ['filtered_changes_remain', true],
            ['ignore_non_php_file', true],
            ['new_php_file', true],
            ['normalized_comments_match', false],
            ['removed_php_file', true],
            ['trimmed_empty_comments_match', false]
        ];
    }

    /**
     * Read test data files into the proper format to pass into the filter() function
     *
     * Looks for test files in __DIR__/<test_class>/_files/<data_location>/<data_type>/<before|after>
     *
     * Return format:
     *   list([<before_file_contents>], [<after_file_contents>])
     *
     * @param string $dataLocation
     * @param string $dataType
     * @return array
     */
    protected function getTestData($dataLocation, $dataType)
    {
        $testDir = __DIR__ . '/' . substr(strrchr(get_class($this), "\\"), 1) . "/_files/$dataLocation";
        $result = [];
        foreach (["before", "after"] as $sourceType) {
            $dataDir = "$testDir/$dataType/$sourceType";
            $testFiles = array_map(function ($file) use ($dataDir) {
                return "$dataDir/$file";
            }, scandir($dataDir));
            $fileContents = [];
            foreach ($testFiles as $filePath) {
                $fileName = pathinfo($filePath, PATHINFO_BASENAME);
                if ($fileName && $fileName !== '.'  && $fileName !== '..') {
                    // Filters expect the array keys to be equivalent relative file paths in different root
                    // directories, so <data_type>/<before|after> needs to be removed from the test paths to make
                    // them match since their relative paths from the root are different
                    $fakePath = $testDir . '/' . $fileName;
                    $fileContents[$fakePath] = file($filePath, FILE_IGNORE_NEW_LINES);
                }
            }
            $result[] = $fileContents;
        }
        return $result;
    }
}
