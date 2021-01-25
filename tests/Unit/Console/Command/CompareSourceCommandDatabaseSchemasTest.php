<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Test\Unit\Console\Command;

use Magento\SemanticVersionChecker\Test\Unit\Console\Command\CompareSourceCommandTest\AbstractTestCaseWithRegExp;

/**
 * Test semantic version checker CLI command dealing with database schemas.
 */
class CompareSourceCommandDatabaseSchemasTest extends AbstractTestCaseWithRegExp
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
     *
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

    /**
     * @return array
     */
    public function changesDataProvider()
    {
        $pathToFixtures = __DIR__ . '/CompareSourceCommandTest/_files/db_schema';
        return [
            'nothing-changed' => [
                $pathToFixtures . '/nothing-changed/source-code-before',
                $pathToFixtures . '/nothing-changed/source-code-after',
                [
                    '/Suggested semantic versioning change: NONE/'
                ],
                'Patch change is detected.'
            ],
            'drop-foreign-key' => [
                $pathToFixtures . '/drop-foreign-key/source-code-before',
                $pathToFixtures . '/drop-foreign-key/source-code-after',
                [
                    '#Database \(MAJOR\)#',
                    '#[\w/]+' . '/drop-foreign-key/source-code-before/Magento/DbSchema/etc/db_schema\.xml:0#',
                    '#unit_test_table/FL_ALLOWED_SEVERITIES\s*\|\s*Foreign key was removed\s*\|\s*M108#',
                    '#[\w/]+' . '/drop-foreign-key/source-code-before/Magento/DbSchema/etc/db_schema_whitelist\.json:0 \| unit_test_table/constraint#',
                    '#unit_test_table/constraint\s*\|\s*Module db schema whitelist reduced \(unit_test_table/constraint\)#'
                ],
                'Major change is detected.'
            ],
            'change-foreign-key' => [
                $pathToFixtures . '/change-foreign-key/source-code-before',
                $pathToFixtures . '/change-foreign-key/source-code-after',
                [
                    '#Database \(MAJOR\)#',
                    '#[\w/]+' . 'change-foreign-key/source-code-before/Magento/DbSchema/etc/db_schema\.xml:0#',
                    '#unit_test_table/FL_ALLOWED_SEVERITIES/referenceTable\s*\|\s*Foreign key was changed\s*\|\s*M205#'
                ],
                'Major change is detected.'
            ],
            'add-foreign-key' => [
                $pathToFixtures . '/add-foreign-key/source-code-before',
                $pathToFixtures . '/add-foreign-key/source-code-after',
                [
                    '#Database \(MAJOR\)#',
                    '#[\w/]+' . 'add-foreign-key/source-code-after/Magento/DbSchema/etc/db_schema\.xml:0#',
                    '#unit_test_table/FL_ALLOWED_SEVERITIES\s*\|\s*Foreign key was added\s*\|\s*M204#'
                ],
                'Major change is detected.'
            ],
            'drop-primary-key' => [
                $pathToFixtures . '/drop-primary-key/source-code-before',
                $pathToFixtures . '/drop-primary-key/source-code-after',
                [
                    '#Database \(MAJOR\)#',
                    '#[\w/]+' . 'drop-primary-key/source-code-before/Magento/DbSchema/etc/db_schema\.xml:0#',
                    '#[\w/]+' . 'drop-primary-key/source-code-before/Magento/DbSchema/etc/db_schema_whitelist.json:0#',
                    '#unit_test_table\s*\|\s*Module db schema whitelist reduced \(unit_test_table\)\.\s*\|\s*M110#',
                    '#unit_test_table/PRIMARY\s*\|\s*Primary key was removed\s*\|\s*M207#'
                ],
                'Major change is detected.'
            ],
            'change-primary-key' => [
                $pathToFixtures . '/change-primary-key/source-code-before',
                $pathToFixtures . '/change-primary-key/source-code-after',
                [
                    '/Database \(MAJOR\)/',
                    '#[\w/]+' . 'change-primary-key/source-code-after/Magento/DbSchema/etc/db_schema\.xml:0#',
                    '#[\w/]+' . 'change-primary-key/source-code-before/Magento/DbSchema/etc/db_schema\.xml:0#',
                    '/unit_test_table\/PRIMARY\s*\|\s*Primary key was changed\s*\|\s*M206/'
                ],
                'Major change is detected.'
            ],
            'add-primary-key' => [
                $pathToFixtures . '/add-primary-key/source-code-before',
                $pathToFixtures . '/add-primary-key/source-code-after',
                [
                    '/Database \(MAJOR\)/',
                    '#[\w/]+' . 'add-primary-key/source-code-after/Magento/DbSchema/etc/db_schema\.xml:0#',
                    '/unit_test_table\/PRIMARY\s*\|\s*Primary key was added\s*\|\s*M205/'
                ],
                'Major change is detected.'
            ],
            'drop-unique-key' => [
                $pathToFixtures . '/drop-unique-key/source-code-before',
                $pathToFixtures . '/drop-unique-key/source-code-after',
                [
                    '/Database \(MAJOR\)/',
                    '/unit_test_table\/UNIQUE_KEY\s*\|\s*Unique key was removed\s*\|\s*M209/',
                    '#unit_test_table/constraint\s*\|\s*Module db schema whitelist reduced \(unit_test_table/constraint\)\.\s*\|\s*M110#',
                    '#[\w/]+' . 'drop-unique-key/source-code-before/Magento/DbSchema/etc/db_schema.xml:0#',
                    '#[\w/]+' . 'drop-unique-key/source-code-before/Magento/DbSchema/etc/db_schema_whitelist.json:0#'
                ],
                'Major change is detected.'
            ],
            'change-unique-key' => [
                $pathToFixtures . '/change-unique-key/source-code-before',
                $pathToFixtures . '/change-unique-key/source-code-after',
                [
                    '/Database \(MAJOR\)/',
                    '/unit_test_table\/UNIQUE_KEY\s*\|\s*Unique key was changed\s*\|\s*M210/',
                    '#[\w/]+' . 'change-unique-key/source-code-before/Magento/DbSchema/etc/db_schema\.xml:0#',
                    '#[\w/]+' . 'change-unique-key/source-code-after/Magento/DbSchema/etc/db_schema\.xml:0#'
                ],
                'Major change is detected.'
            ],
            'add-unique-key' => [
                $pathToFixtures . '/add-unique-key/source-code-before',
                $pathToFixtures . '/add-unique-key/source-code-after',
                [
                    '/Database \(MAJOR\)/',
                    '/unit_test_table\/UNIQUE_KEY\s*\|\s*Unique key was added\s*\|\s*M208/',
                    '#[\w/]+' . 'add-unique-key/source-code-after/Magento/DbSchema/etc/db_schema\.xml:0#'
                ],
                'Major change is detected.'
            ],
            'column-removed' => [
                $pathToFixtures . '/column-removed/source-code-before',
                $pathToFixtures . '/column-removed/source-code-after',
                [
                    '/Database \(MAJOR\)/',
                    '/unit_test_table\/time_occurred\s*\|\s*Column was removed\s*\|\s*M107/',
                    '/Module db schema whitelist reduced \(unit\_test\_table\/column\).\s*\|\s*M110/',
                    '#[\w/]+' . 'column-removed/source-code-before/Magento/DbSchema/etc/db_schema_whitelist\.json:0#',
                    '#[\w/]+' . 'column-removed/source-code-before/Magento/DbSchema/etc/db_schema\.xml:0#'
                ],
                'Major change is detected.'
            ],
            'column-added' => [
                $pathToFixtures . '/column-added/source-code-before',
                $pathToFixtures . '/column-added/source-code-after',
                [
                    '/Database \(MINOR\)/',
                    '#[\w/]+' . 'column-added/source-code-after/Magento/DbSchema/etc/db_schema\.xml:0#',
                    '/unit_test_table\/time_occurred\s*\|\s*Column was added\s*\|\s*M203/'
                ],
                'Minor change is detected.'
            ],
            'table-dropped' => [
                $pathToFixtures . '/table-dropped/source-code-before',
                $pathToFixtures . '/table-dropped/source-code-after',
                [
                    '/Database \(MAJOR\)/',
                    '/other_unit_test_table\s*\|\s*Table was dropped\s*\|\s*M104/',
                    '/Module db schema whitelist reduced \(other\_unit\_test\_table\).\s*\|\s*M110/',
                    '#[\w/]+' . '/table-dropped/source-code-before/Magento/DbSchema/etc/db_schema\.xml:0#',
                    '#[\w/]+' . '/table-dropped/source-code-before/Magento/DbSchema/etc/db_schema_whitelist\.json:0#'
                ],
                'Major change is detected.'
            ],
            'table-added' => [
                $pathToFixtures . '/table-added/source-code-before',
                $pathToFixtures . '/table-added/source-code-after',
                [
                    '/Database \(MINOR\)/',
                    '/other_unit_test_table\s*\|\s*Table was added\s*\|\s*M202/',
                    '#other_table\s*\|\s*Table was added\s*\|\s*M202#',
                    '#other_table\s*\|\s*Whitelist do not have table other_table declared in db_schema\.xml\s*\|\s*M109#',
                    '#[\w/]+' . '/table-added/source-code-after/Magento/DbSchema/etc/db_schema\.xml:0#',
                    '#[\w/]+' . '/table-added/source-code-after/Magento/DbSchema/etc/db_schema_whitelist\.json:0#',

                ],
                'Minor change is detected.'
            ],
            'table-changed' => [
                $pathToFixtures . '/table-changed/source-code-before',
                $pathToFixtures . '/table-changed/source-code-after',
                [
                    '/Database \(MAJOR\)/',
                    '/unit_test_table\s*\|\s*Table was dropped\s*\|\s*M104/',
                    '#[\w/]+' . 'table-changed/source-code-after/Magento/DbSchema/etc/db_schema\.xml:0#',
                    '#[\w/]+' . 'table-changed/source-code-before/Magento/DbSchema/etc/db_schema\.xml:0#',
                    '#[\w/]+' . 'table-changed/source-code-before/Magento/DbSchema/etc/db_schema_whitelist\.json:0#',
                    '/unit_test_table\s*\|\s*Module db schema whitelist reduced \(unit\_test\_table\).\s*\|\s*M110/',
                    '/new_unit_test_table\s*\|\s*Table was added\s*\|\s*M202/'
                ],
                'Major change is detected.'
            ],
            'table-resource-changed' => [
                $pathToFixtures . '/table-resource-changed/source-code-before',
                $pathToFixtures . '/table-resource-changed/source-code-after',
                [
                    '/Database \(MAJOR\)/',
                    '#[\w/]+' . '/table-resource-changed/source-code-before/Magento/DbSchema/etc/db_schema\.xml:0#',
                    '/unit_test_table\s*\|\s*Table chard was changed from default to sales\s*\|\s*M105/'
                ],
                'Major change is detected.'
            ],
            'whitelist-was-reduced' => [
                $pathToFixtures . '/whitelist-was-reduced/source-code-before',
                $pathToFixtures . '/whitelist-was-reduced/source-code-after',
                [
                    '/Database \(MAJOR\)/',
                    '/Magento\/DbSchema\/etc\/db_schema_whitelist.json:0\s*\|\s*unit_test_table\s*\|\s*Module db schema whitelist reduced \(unit\_test\_table\).\s*\|\s*M110/',
                    '/Magento\/DbSchemaSecond\/etc\/db_schema_whitelist\.json:0\s*\|\s*unit_test_table3\s*\|\s*Module db schema whitelist reduced \(unit\_test\_table3\).\s*\|\s*M110/',
                    '/Magento\/DbSchemaSecond\/etc\/db_schema_whitelist\.json:0\s*\|\s*unit_test_table2\s*\|\s*Module db schema whitelist reduced \(unit\_test\_table2\).\s*\|\s*M110/',
                    '/Magento\/DbSchema\/etc\/db_schema_whitelist.json:0\s*\|\s*unit_test_table3\s*\|\s*Module db schema whitelist reduced \(unit\_test\_table3\).\s*\|\s*M110/',
                    '#[\w/]+' . '/whitelist-was-reduced/source-code-before/Magento/DbSchemaSecond/etc/db_schema\.xml:0#',
                    '#[\w/]+' . '/whitelist-was-reduced/source-code-before/Magento/DbSchema/etc/db_schema\.xml:0#'
                ],
                'Major change is detected.'
            ],
            'whitelist-was-removed' => [
                $pathToFixtures . '/whitelist-was-removed/source-code-before',
                $pathToFixtures . '/whitelist-was-removed/source-code-after',
                [
                    '/Database \(MAJOR\)/',
                    '/Magento\/DbSchema\/etc\/db_schema_whitelist\.json:0\s*\|\s*Magento_DbSchema\s*|\s*Db Whitelist from module Magento_DbSchema was removed\s*\|\s*M109/'
                ],
                'Major change is detected.'
            ]
        ];
    }
}
