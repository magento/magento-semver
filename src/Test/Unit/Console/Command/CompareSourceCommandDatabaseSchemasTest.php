<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\SemanticVersionChecker\Test\Unit\Console\Command;

use Magento\Tools\SemanticVersionChecker\Test\Unit\Console\Command\CompareSourceCommandTest\AbstractTestCase;

/**
 * Test semantic version checker CLI command dealing with database schemas.
 */
class CompareSourceCommandDatabaseSchemasTest extends AbstractTestCase
{
    /**
     * Test semantic version checker CLI command for changes of the database schema.
     *
     * @param string $pathToSourceCodeBefore
     * @param string $pathToSourceCodeAfter
     * @param string[] $expectedLogEntries
     * @param string $expectedOutput
     * @param bool $shouldSkipTest
     * @dataProvider changesDataProvider
     * @return void
     * @throws \Exception
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
        $pathToFixtures = __DIR__ . '/CompareSourceCommandTest/_files/db_schema';
        return [
            'nothing-changed' => [
                $pathToFixtures . '/nothing-changed/source-code-before',
                $pathToFixtures . '/nothing-changed/source-code-after',
                [
                    'Suggested semantic versioning change: NONE'
                ],
                'Patch change is detected.'
            ],
            'drop-foreign-key' => [
                $pathToFixtures . '/drop-foreign-key/source-code-before',
                $pathToFixtures . '/drop-foreign-key/source-code-after',
                [
                    'Database (MAJOR)',
                    'unit_test_table/FL_ALLOWED_SEVERITIES | Foreign key removed from declaration but it may have had business logic in onDelete statement | M108'
                ],
                'Major change is detected.'
            ],
            'drop-key' => [
                $pathToFixtures . '/drop-key/source-code-before',
                $pathToFixtures . '/drop-key/source-code-after',
                [
                    'Database (MINOR)',
                    'unit_test_table/PRIMARY | Key was dropped. But it can be used for 3-rd parties foreign keys | M106'
                ],
                'Minor change is detected.'
            ],
            'column-removed' => [
                $pathToFixtures . '/column-removed/source-code-before',
                $pathToFixtures . '/column-removed/source-code-after',
                [
                    'Database (MAJOR)',
                    'unit_test_table/time_occurred | Column was removed                  | M107',
                    'Module db schema whitelist reduced. | M110'
                ],
                'Major change is detected.'
            ],
            'table-dropped' => [
                $pathToFixtures . '/table-dropped/source-code-before',
                $pathToFixtures . '/table-dropped/source-code-after',
                [
                    'Database (MAJOR)',
                    'other_unit_test_table | Table was dropped                                                           | M104',
                    'Module db schema whitelist reduced.                                         | M110',
                    'other_unit_test_table | Whitelist do not have table other_unit_test_table declared in db_schema.xml | M109'
                ],
                'Major change is detected.'
            ],
            'table-changed' => [
                $pathToFixtures . '/table-changed/source-code-before',
                $pathToFixtures . '/table-changed/source-code-after',
                [
                    'Database (MAJOR)',
                    'unit_test_table | Table was dropped                                                     | M104',
                    'Module db schema whitelist reduced.                                   | M110',
                    'unit_test_table | Whitelist do not have table unit_test_table declared in db_schema.xml | M109'
                ],
                'Major change is detected.'
            ],
            'table-resource-changed' => [
                $pathToFixtures . '/table-resource-changed/source-code-before',
                $pathToFixtures . '/table-resource-changed/source-code-after',
                [
                    'Database (MAJOR)',
                    'unit_test_table | Table chard was changed from default to sales | M105'
                ],
                'Major change is detected.'
            ],
            'whitelist-was-reduced' => [
                $pathToFixtures . '/whitelist-was-reduced/source-code-before',
                $pathToFixtures . '/whitelist-was-reduced/source-code-after',
                [
                    'Database (MAJOR)',
                    'Module db schema whitelist reduced. | M110'
                ],
                'Major change is detected.'
            ],
            'whitelist-was-removed' => [
                $pathToFixtures . '/whitelist-was-removed/source-code-before',
                $pathToFixtures . '/whitelist-was-removed/source-code-after',
                [
                    'Database (MINOR)',
                    'Magento_DbSchema | Db Whitelist from module Magento_DbSchema was removed | M109'
                ],
                'Minor change is detected.'
            ]
        ];
    }
}
