<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tools\SemanticVersionChecker\Test\Unit\Console\Command;

use Magento\Tools\SemanticVersionChecker\Test\Unit\Console\Command\CompareSourceCommandTest\AbstractTestCase;

/**
 * Test semantic version checker CLI command dealing with di.xml
 */
class CompareSourceCommandDiXmlTest extends AbstractTestCase
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
        $pathToFixtures = __DIR__.'/CompareSourceCommandTest/_files/di_xml';

        return [
            'no-change' => [
                $pathToFixtures.'/no-change/source-code-before',
                $pathToFixtures.'/no-change/source-code-after',
                [

                ],
                ''
            ],
            'moved-to-global' => [
                $pathToFixtures.'/moved-to-global/source-code-before',
                $pathToFixtures.'/moved-to-global/source-code-after',
                [
                    'Suggested semantic versioning change: NONE',
                ],
                'Patch change is detected.',
            ],
            'moved-to-specific' => [
                $pathToFixtures.'/moved-to-specific/source-code-before',
                $pathToFixtures.'/moved-to-specific/source-code-after',
                [
                    'Suggested semantic versioning change: MAJOR',
                ],
                'Major change is detected.',
            ],
            'remove-type' => [
                $pathToFixtures.'/remove-type/source-code-before',
                $pathToFixtures.'/remove-type/source-code-after',
                [
                    'Suggested semantic versioning change: MAJOR',
                ],
                'Major change is detected.',
            ],
            'change-type' => [
                $pathToFixtures.'/change-type/source-code-before',
                $pathToFixtures.'/change-type/source-code-after',
                [
                    'Suggested semantic versioning change: MAJOR',
                ],
                'Major change is detected.',
            ],
            'change-name' => [
                $pathToFixtures.'/change-type/source-code-before',
                $pathToFixtures.'/change-type/source-code-after',
                [
                    'Suggested semantic versioning change: MAJOR',
                ],
                'Major change is detected.',
            ],
        ];
    }
}
