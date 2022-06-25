<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Console\Command;

use Magento\SemanticVersionChecker\Test\Unit\Console\Command\CompareSourceCommandTest\AbstractTestCaseWithRegExp;

/**
 * Test semantic version checker CLI command dealing with di.xml
 */
class CompareSourceCommandDiXmlTest extends AbstractTestCaseWithRegExp
{
    /**
     * Test semantic version checker CLI command for changes of the database schema.
     *
     * @param string $pathToSourceCodeBefore
     * @param string $pathToSourceCodeAfter
     * @param string[] $expectedLogEntries
     * @param string $expectedOutput
     * @param bool $shouldSkipTest
     * @return void
     * @throws \Exception
     * @dataProvider changesDataProvider
     */
    public function testExecute(
        $pathToSourceCodeBefore,
        $pathToSourceCodeAfter,
        $expectedLogEntries,
        $expectedOutput,
        $shouldSkipTest = false
    ) {
        $this->doTestExecute(
            $pathToSourceCodeBefore,
            $pathToSourceCodeAfter,
            $expectedLogEntries,
            $expectedOutput,
            $shouldSkipTest
        );
    }

    public function changesDataProvider()
    {
        $pathToFixtures = __DIR__ . '/CompareSourceCommandTest/_files/di_xml';

        return [
            'no-change' => [
                $pathToFixtures . '/no-change/source-code-before',
                $pathToFixtures . '/no-change/source-code-after',
                [
                    '#Suggested semantic versioning change: NONE#',
                ],
                ''
            ],
            'moved-to-global' => [
                $pathToFixtures . '/moved-to-global/source-code-before',
                $pathToFixtures . '/moved-to-global/source-code-after',
                [
                    '#Suggested semantic versioning change: NONE#',
                ],
                'Patch change is detected.',
            ],
            'moved-to-specific' => [
                $pathToFixtures . '/moved-to-specific/source-code-before',
                $pathToFixtures . '/moved-to-specific/source-code-after',
                [
                    '#Suggested semantic versioning change: MAJOR#',
                    '#MAJOR\s*\|\s' . '[\w/]+'  . '/moved-to-specific/source-code-before/Magento/TestModule/etc/di\.xml:0#',
                    '#scope\s*\|\s*Virtual Type was changed\s*\|\s*M201#'
                ],
                'Major change is detected.',
            ],
            'remove-type' => [
                $pathToFixtures . '/remove-type/source-code-before',
                $pathToFixtures . '/remove-type/source-code-after',
                [
                    '#Suggested semantic versioning change: MAJOR#',
                    '#MAJOR\s*\|\s' . '[\w/]+' . 'remove-type/source-code-before/Magento/TestModule/etc/di\.xml:0#',
                    '#customCacheInstance2\s*\|\s*Virtual Type was removed\s*\|\s*M200\s*#'
                ],
                'Major change is detected.',
            ],
            'change-type' => [
                $pathToFixtures . '/change-type/source-code-before',
                $pathToFixtures . '/change-type/source-code-after',
                [
                    '#Suggested semantic versioning change: MAJOR#',
                    '#MAJOR\s*\|\s' . '[\w/]+' . '/change-type/source-code-before/Magento/TestModule/etc/di\.xml:0#',
                    '#type\s*\|\s*Virtual Type was changed\s*\|\s*M201#'
                ],
                'Major change is detected.',
            ],
            'change-name' => [
                $pathToFixtures . '/change-name/source-code-before',
                $pathToFixtures . '/change-name/source-code-after',
                [
                    '#Suggested semantic versioning change: MAJOR#',
                    '#MAJOR\s*\|\s*' . '[\w/]+' . '/change-name/source-code-before/Magento/TestModule/etc/di\.xml:0#',
                    '#cacheInstance\s*\|\s*Virtual Type was removed\s*\|\s*M200#'
                ],
                'Major change is detected.',
            ],
            'removing-leading-slash-from-type' => [
                $pathToFixtures . '/removing-leading-slash-from-type/source-code-before',
                $pathToFixtures . '/removing-leading-slash-from-type/source-code-after',
                [
                    '#Suggested semantic versioning change: NONE#',
                ],
                'Patch change is detected.',
            ],
        ];
    }
}
