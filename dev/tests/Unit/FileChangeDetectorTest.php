<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit;

use Magento\SemanticVersionChecker\FileChangeDetector;
use Magento\SemanticVersionChecker\Filter\AllowedChangeFilter\ChangedFileFilterInterface;
use PHPUnit\Framework\TestCase;

class FileChangeDetectorTest extends TestCase
{
    /**
     * Verify that only differing files (added, removed, or changed) are returned and ignored files are ignored
     *
     * @return void
     */
    public function testGetChangedFiles()
    {
        $beforeDir = __DIR__ . '/FileChangeDetectorTest/_files/before';
        $afterDir = __DIR__ . '/FileChangeDetectorTest/_files/after';
        $detector = new FileChangeDetector($beforeDir, $afterDir);

        $expected = [
            "$beforeDir/removed_file.txt",
            "$beforeDir/changed_text_file.txt",
            "$beforeDir/changed_json_file.json",
            "$afterDir/added_file.txt",
            "$afterDir/changed_text_file.txt",
            "$afterDir/changed_json_file.json"
        ];

        $changedFiles = $detector->getChangedFiles();
        $this->assertEmpty(array_diff($expected, $changedFiles));
        $this->assertEmpty(array_diff($changedFiles, $expected));
    }

    /**
     * Verify that additional file filters to ignore certain kinds of files or changes are applied in order
     *
     * @return void
     */
    public function testGetChangedFilesAppliesFilters()
    {
        $beforeDir = __DIR__ . '/FileChangeDetectorTest/_files/before';
        $afterDir = __DIR__ . '/FileChangeDetectorTest/_files/after';

        $jsonFilter = $this->getMockBuilder(ChangedFileFilterInterface::class)->getMockForAbstractClass();
        $jsonFilter->expects($this->once())->method('filter')->will($this->returnCallback(
            function (&$beforeFileContents, &$afterFileContents) {
                $expectedBefore = [
                    'removed_file.txt',
                    'changed_text_file.txt',
                    'changed_json_file.json'
                ];
                $expectedAfter = [
                    'added_file.txt',
                    'changed_text_file.txt',
                    'changed_json_file.json'
                ];

                $this->assertEmpty(array_diff($expectedBefore, array_keys($beforeFileContents)));
                $this->assertEmpty(array_diff(array_keys($beforeFileContents), $expectedBefore));

                $this->assertEmpty(array_diff($expectedAfter, array_keys($afterFileContents)));
                $this->assertEmpty(array_diff(array_keys($afterFileContents), $expectedAfter));

                $beforeFileContents = array_filter($beforeFileContents, function ($key) {
                    return $key != 'changed_json_file.json';
                }, ARRAY_FILTER_USE_KEY);
                $afterFileContents = array_filter($afterFileContents, function ($key) {
                    return $key != 'changed_json_file.json';
                }, ARRAY_FILTER_USE_KEY);
            }
        ));

        $txtFilter = $this->getMockBuilder(ChangedFileFilterInterface::class)->getMockForAbstractClass();
        $txtFilter->expects($this->once())->method('filter')->will($this->returnCallback(
            function (&$beforeFileContents, &$afterFileContents) {
                $expectedBefore = [
                    'removed_file.txt',
                    'changed_text_file.txt'
                ];
                $expectedAfter = [
                    'added_file.txt',
                    'changed_text_file.txt'
                ];


                $this->assertEmpty(array_diff($expectedBefore, array_keys($beforeFileContents)));
                $this->assertEmpty(array_diff(array_keys($beforeFileContents), $expectedBefore));

                $this->assertEmpty(array_diff($expectedAfter, array_keys($afterFileContents)));
                $this->assertEmpty(array_diff(array_keys($afterFileContents), $expectedAfter));

                $beforeFileContents = array_filter($beforeFileContents, function ($key) {
                    return $key != 'changed_text_file.txt';
                }, ARRAY_FILTER_USE_KEY);
                $afterFileContents = array_filter($afterFileContents, function ($key) {
                    return $key != 'changed_text_file.txt';
                }, ARRAY_FILTER_USE_KEY);
            }
        ));

        $detector = new FileChangeDetector($beforeDir, $afterDir, [$jsonFilter, $txtFilter]);

        $expected = [
            "$beforeDir/removed_file.txt",
            "$afterDir/added_file.txt"
        ];

        $changedFiles = $detector->getChangedFiles();
        $this->assertEmpty(array_diff($expected, $changedFiles));
        $this->assertEmpty(array_diff($changedFiles, $expected));
    }
}
