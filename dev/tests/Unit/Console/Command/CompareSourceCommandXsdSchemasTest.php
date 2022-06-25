<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Console\Command;

use Magento\SemanticVersionChecker\Test\Unit\Console\Command\CompareSourceCommandTest\AbstractTestCase;

/**
 * Test semantic version checker CLI command dealing with xsd schema files
 */
class CompareSourceCommandXsdSchemasTest extends AbstractTestCase
{
    /**
     * Test semantic version checker CLI command for changes of the xsd schemes.
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
        $pathToFixtures = __DIR__ . '/CompareSourceCommandTest/_files/xsd-schema';

        return [
            'no-change'                  => [
                $pathToFixtures . '/no-change/source-code-before',
                $pathToFixtures . '/no-change/source-code-after',
                [
                    'Suggested semantic versioning change: NONE',
                ],
                'None change is detected.',
            ],
            'optional-node-added'        => [
                $pathToFixtures . '/optional-node-added/source-code-before',
                $pathToFixtures . '/optional-node-added/source-code-after',
                [
                    'Suggested semantic versioning change: MINOR',
                    'optional-node-added/source-code-after/Magento/TestModule/etc/test-schema.xsd:0',
                    'addedOptionalElement | An optional node was added | M0133',
                ],
                'Minor change is detected.',
            ],
            'optional-attribute-added'   => [
                $pathToFixtures . '/optional-attribute-added/source-code-before',
                $pathToFixtures . '/optional-attribute-added/source-code-after',
                [
                    'Suggested semantic versioning change: MINOR',
                    'optional-attribute-added/source-code-after/Magento/TestModule/etc/test-schema.xsd:0',
                    'optionalAttribute | An optional attribute was added | M0134',
                ],
                'Minor change is detected.',
            ],
            'required-node-added'        => [
                $pathToFixtures . '/required-node-added/source-code-before',
                $pathToFixtures . '/required-node-added/source-code-after',
                [
                    'Suggested semantic versioning change: MAJOR',
                    'required-node-added/source-code-after/Magento/TestModule/etc/test-schema.xsd:0',
                    'addedRequiredElement | A required node was added | M0135',
                ],
                'Major change is detected.',
            ],
            'required-attribute-added'   => [
                $pathToFixtures . '/required-attribute-added/source-code-before',
                $pathToFixtures . '/required-attribute-added/source-code-after',
                [
                    'Suggested semantic versioning change: MAJOR',
                    'required-attribute-added/source-code-after/Magento/TestModule/etc/test-schema.xsd:0',
                    'requiredAttribute | A required attribute was added | M0136',
                ],
                'Major change is detected.',
            ],
            'node-removed'               => [
                $pathToFixtures . '/node-removed/source-code-before',
                $pathToFixtures . '/node-removed/source-code-after',
                [
                    'Suggested semantic versioning change: MAJOR',
                    'node-removed/source-code-before/Magento/TestModule/etc/test-schema.xsd:0 | requiredElement | A node was removed | M0137',
                    'node-removed/source-code-before/Magento/TestModule/etc/test-schema.xsd:0 | optionalElement | A node was removed | M0137',
                ],
                'Major change is detected.',
            ],
            'attribute-removed'          => [
                $pathToFixtures . '/attribute-removed/source-code-before',
                $pathToFixtures . '/attribute-removed/source-code-after',
                [
                    'Suggested semantic versioning change: MAJOR',
                    'attribute-removed/source-code-before/Magento/TestModule/etc/test-schema.xsd:0 | requiredAttribute | An attribute was removed | M0138',
                    'attribute-removed/source-code-before/Magento/TestModule/etc/test-schema.xsd:0 | optionalAttribute | An attribute was removed | M0138',
                ],
                'Major change is detected.',
            ],
            'schema-declaration-removed' => [
                $pathToFixtures . '/schema-declaration-removed/source-code-before',
                $pathToFixtures . '/schema-declaration-removed/source-code-after',
                [
                    'Suggested semantic versioning change: MAJOR',
                    'schema-declaration-removed/source-code-before/Magento/TestModule/etc/test-schema.xsd:0',
                    '/etc/test-schema.xsd | A schema declaration was removed | M0139'
                ],
                'Major change is detected.',
            ],
            'schema-declaration-added'   => [
                $pathToFixtures . '/schema-declaration-added/source-code-before',
                $pathToFixtures . '/schema-declaration-added/source-code-after',
                [
                    'Suggested semantic versioning change: MINOR',
                    'schema-declaration-added/source-code-after/Magento/TestModule/etc/test-schema.xsd:0',
                    '/etc/test-schema.xsd | A schema declaration was added | M0140',
                ],
                'Minor change is detected.',
            ],
            'module-added'               => [
                $pathToFixtures . '/module-added/source-code-before',
                $pathToFixtures . '/module-added/source-code-after',
                [
                    'Suggested semantic versioning change: MINOR',
                    'module-added/source-code-after/Magento/TestModule/etc/test-schema.xsd:0',
                    '/etc/test-schema.xsd | A schema declaration was added | M0140',
                ],
                'Minor change is detected.',
            ],
            'module-removed'             => [
                $pathToFixtures . '/module-removed/source-code-before',
                $pathToFixtures . '/module-removed/source-code-after',
                [
                    'Suggested semantic versioning change: MAJOR',
                    'module-removed/source-code-before/Magento/TestModule/etc/test-schema.xsd:0',
                    '/etc/test-schema.xsd | A schema declaration was removed | M0139',
                ],
                'Major change is detected.',
            ],
        ];
    }
}
