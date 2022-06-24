<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Console\Command;

use Magento\SemanticVersionChecker\Test\Unit\Console\Command\CompareSourceCommandTest\AbstractTestCase;

class HierarchyTest extends AbstractTestCase
{
    /**
     * Test semantic version checker CLI command for changes on interfaces that do not have the <kbd>api</kbd> annotation.
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
        $pathToFixtures = __DIR__ . '/HierarchyTest/_files';
        return [
            'public-method-removed-from-non-api-parent-class-extended-by-api-class'        => [
                $pathToFixtures . '/public-method-removed-from-non-api-parent-class-extended-by-api-class/source-code-before',
                $pathToFixtures . '/public-method-removed-from-non-api-parent-class-extended-by-api-class/source-code-after',
                [
                    'Suggested semantic versioning change: MAJOR',
                ],
                'Major change is detected.',
            ],
            'public-method-removed-from-non-api-trait-used-by-api-class'   => [
                $pathToFixtures . '/public-method-removed-from-non-api-trait-used-by-api-class/source-code-before',
                $pathToFixtures . '/public-method-removed-from-non-api-trait-used-by-api-class/source-code-after',
                [
                    'Suggested semantic versioning change: MAJOR',
                ],
                'Major change is detected.',
            ],
            'public-method-removed-from-non-api-interface-implemented-by-class'   => [
                $pathToFixtures . '/public-method-removed-from-non-api-interface-implemented-by-class/source-code-before',
                $pathToFixtures . '/public-method-removed-from-non-api-interface-implemented-by-class/source-code-after',
                [
                    'Suggested semantic versioning change: MAJOR',
                ],
                'Major change is detected.',
            ],
            'public-method-removed-from-non-api-interface-extented-by-api-interface' => [
                $pathToFixtures . '/public-method-removed-from-non-api-interface-extented-by-api-interface/source-code-before',
                $pathToFixtures . '/public-method-removed-from-non-api-interface-extented-by-api-interface/source-code-after',
                [
                    'Suggested semantic versioning change: MAJOR',
                ],
                'Major change is detected.',
            ],
            'public-method-removed-from-non-api-trait-used-by-api-trait' => [
                $pathToFixtures . '/public-method-removed-from-non-api-trait-used-by-api-trait/source-code-before',
                $pathToFixtures . '/public-method-removed-from-non-api-trait-used-by-api-trait/source-code-after',
                [
                    'Suggested semantic versioning change: MAJOR',
                ],
                'Major change is detected.',
            ],
        ];
    }
}
