<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Console\Command;

use Magento\SemanticVersionChecker\Test\Unit\Console\Command\CompareSourceCommandTest\AbstractTestCase;

/**
 * Test semantic version checker CLI command dealing with layout xml.
 */
class CompareSourceCommandLayoutTest extends AbstractTestCase
{
    /**
     * Test semantic version checker CLI command for changes of the database schema.
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
        $pathToFixtures = __DIR__.'/CompareSourceCommandTest/_files/layout_xml';

        return [
            'block_remove' => [
                $pathToFixtures.'/block_remove/source-code-before',
                $pathToFixtures.'/block_remove/source-code-after',
                [
                    'Suggested semantic versioning change: MAJOR',
                ],
                'Major change is detected.',
            ],
            'container_remove' => [
                $pathToFixtures.'/container_remove/source-code-before',
                $pathToFixtures.'/container_remove/source-code-after',
                [
                    'Suggested semantic versioning change: MAJOR',
                ],
                'Major change is detected.',
            ],
            'update_remove' => [
                $pathToFixtures.'/update_remove/source-code-before',
                $pathToFixtures.'/update_remove/source-code-after',
                [
                    'Suggested semantic versioning change: MAJOR',
                ],
                'Major change is detected.',
            ],
        ];
    }
}
