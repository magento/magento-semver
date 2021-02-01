<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Console\Command;

use Magento\SemanticVersionChecker\Test\Unit\Console\Command\CompareSourceCommandTest\AbstractTestCase;

/**
 * Test semantic version checker CLI command dealing with <kbd>system.xml</kbd>
 */
class CompareSourceCommandSystemXmlTest extends AbstractTestCase
{
    /**
     * Test semantic version checker CLI command for changes in <kbd>system.xml</kbd> files.
     *
     * @param string $pathToSourceCodeBefore
     * @param string $pathToSourceCodeAfter
     * @param string[] $expectedLogEntries
     * @param string $expectedOutput
     * @param string[] $unexpectedLogEntries
     * @return void
     * @throws \Exception
     * @dataProvider changesDataProvider
     */
    public function testExecute(
        $pathToSourceCodeBefore,
        $pathToSourceCodeAfter,
        $expectedLogEntries,
        $expectedOutput,
        $unexpectedLogEntries = []
    ) {
        $this->doTestExecute(
            $pathToSourceCodeBefore,
            $pathToSourceCodeAfter,
            $expectedLogEntries,
            $expectedOutput,
            $unexpectedLogEntries
        );
    }

    public function changesDataProvider()
    {
        $pathToFixtures = __DIR__ . '/CompareSourceCommandTest/_files/system_xml';

        return [
            'no-change'       => [
                $pathToFixtures . '/no-change/source-code-before',
                $pathToFixtures . '/no-change/source-code-after',
                [
                    'Suggested semantic versioning change: NONE',
                ],
                'None change is detected.',
            ],
            'file-added'      => [
                $pathToFixtures . '/file-added/source-code-before',
                $pathToFixtures . '/file-added/source-code-after',
                [
                    'Suggested semantic versioning change: MINOR',
                    'Magento/TestModule/etc/adminhtml/system.xml:0 | system.xml | System configuration file was added | M300',
                ],
                'Minor change is detected.',
            ],
            'file-removed'    => [
                $pathToFixtures . '/file-removed/source-code-before',
                $pathToFixtures . '/file-removed/source-code-after',
                [
                    'Suggested semantic versioning change: MAJOR',
                    'Magento/TestModule/etc/adminhtml/system.xml:0 | system.xml | System configuration file was added | M301',
                ],
                'Major change is detected.',
            ],
            'section-added'   => [
                $pathToFixtures . '/section-added/source-code-before',
                $pathToFixtures . '/section-added/source-code-after',
                [
                    'Suggested semantic versioning change: MINOR',
                    'Magento/TestModule/etc/adminhtml/system.xml:0 | added_section | A section-node was added | M306',
                ],
                'Minor change is detected.',
            ],
            'section-removed' => [
                $pathToFixtures . '/section-removed/source-code-before',
                $pathToFixtures . '/section-removed/source-code-after',
                [
                    'Suggested semantic versioning change: MAJOR',
                    'Magento/TestModule/etc/adminhtml/system.xml:0 | removed_section | a section-node was removed | MM307',
                ],
                'Major change is detected.',
            ],
            'group-added'     => [
                $pathToFixtures . '/group-added/source-code-before',
                $pathToFixtures . '/group-added/source-code-after',
                [
                    'Suggested semantic versioning change: MINOR',
                    'Magento/TestModule/etc/adminhtml/system.xml:0 | magento_testmodule/added_group | A group-node was added | M304',
                ],
                'Minor change is detected.',
            ],
            'group-removed'   => [
                $pathToFixtures . '/group-removed/source-code-before',
                $pathToFixtures . '/group-removed/source-code-after',
                [
                    'Suggested semantic versioning change: MAJOR',
                    'Magento/TestModule/etc/adminhtml/system.xml:0 | magento_testmodule/removed_group | A group-node was removed | M305',
                ],
                'Major change is detected.',
            ],
            'field-added'     => [
                $pathToFixtures . '/field-added/source-code-before',
                $pathToFixtures . '/field-added/source-code-after',
                [
                    'Suggested semantic versioning change: MINOR',
                    'Magento/TestModule/etc/adminhtml/system.xml:0 | magento_testmodule/general/added_field | A field-node was added | M302',
                ],
                'Minor change is detected.',
            ],
            'field-removed'   => [
                $pathToFixtures . '/field-removed/source-code-before',
                $pathToFixtures . '/field-removed/source-code-after',
                [
                    'Suggested semantic versioning change: MAJOR',
                    'Magento/TestModule/etc/adminhtml/system.xml:0 | magento_testmodule/general/removed_field | A field-node was removed | M303',
                ],
                'Major change is detected.',
            ],
        ];
    }
}
