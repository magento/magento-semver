<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Console\Command;

use Magento\SemanticVersionChecker\Test\Unit\Console\Command\CompareSourceCommandTest\AbstractTestCaseWithRegExp;

/**
 * Test semantic version checker CLI command dealing with layout xml.
 */
class CompareSourceCommandLayoutTest extends AbstractTestCaseWithRegExp
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
        $pathToFixtures = __DIR__ . '/CompareSourceCommandTest/_files/layout_xml';
        getcwd();

        return [
            'block_remove' => [
                $pathToFixtures . '/block_remove/source-code-before',
                $pathToFixtures . '/block_remove/source-code-after',
                [
                    '#Suggested semantic versioning change: MAJOR#',
                    '#MAJOR\s*\|\s' .  '[\w/]+' . '/block_remove/source-code-before/Magento/Customer/view/adminhtml/layout/customer_index_viewwishlist\.xml:0#',
                    '#admin\.customer\.view\.wishlist\s*\|\s*Block was removed\s*\|\s*M220#'
                ],
                'Major change is detected.',
            ],
            'container_remove' => [
                $pathToFixtures . '/container_remove/source-code-before',
                $pathToFixtures . '/container_remove/source-code-after',
                [
                    '#Suggested semantic versioning change: MAJOR#',
                    '#MAJOR\s*\|\s*' . '[\w/]+' . '/container_remove/source-code-before/Magento/Customer/view/adminhtml/layout/customer_index_viewwishlist\.xml:0#',
                    '#root\s*\|\s*Container was removed\s*\|\s*M221#'
                ],
                'Major change is detected.',
            ],
            'update_remove' => [
                $pathToFixtures . '/update_remove/source-code-before',
                $pathToFixtures . '/update_remove/source-code-after',
                [
                    '#Suggested semantic versioning change: MAJOR#',
                    '#MAJOR\s*\|\s*' . '[\w/]+' . '/update_remove/source-code-before/Magento/ConfigurableProduct/view/adminhtml/layout/catalog_product_configurable\.xml:0#',
                    '#catalog_product_superconfig_config\s*\|\s*An Update was removed\s*\|\s*M222#'
                ],
                'Major change is detected.',
            ],
        ];
    }
}
