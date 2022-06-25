<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Console\Command;

use Magento\SemanticVersionChecker\Test\Unit\Console\Command\CompareSourceCommandTest\AbstractTestCase;

/**
 * Test semantic version checker CLI command dealing with non-API interfaces.
 */
class CompareSourceCommandNonApiInterfacesTest extends AbstractTestCase
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
        $pathToFixtures = __DIR__ . '/CompareSourceCommandTest/_files/non-api-interface';
        return [
            'non-api-interface-new-interface' => [
                $pathToFixtures . '/new-interface/source-code-before',
                $pathToFixtures . '/new-interface/source-code-after',
                [
                    'Interface (PATCH)',
                    'Test\Vcs\TestAInterface | Interface was added. | V032'
                ],
                'Patch change is detected.'
            ],
            'non-api-interface-new-method' => [
                $pathToFixtures . '/new-method/source-code-before',
                $pathToFixtures . '/new-method/source-code-after',
                [
                    'Interface (PATCH)',
                    'Test\Vcs\TestInterface::testMethod | [public] Method has been added. | V034'
                ],
                'Patch change is detected.'
            ],
            'non-api-interface-new-required-method-parameter' => [
                $pathToFixtures . '/new-required-method-parameter/source-code-before',
                $pathToFixtures . '/new-required-method-parameter/source-code-after',
                [
                    'Interface (PATCH)',
                    'Test\Vcs\TestInterface::testMethodA | [public] Method parameter added. | V036'
                ],
                'Patch change is detected.'
            ],
            'non-api-interface-removed-interface' => [
                $pathToFixtures . '/removed-interface/source-code-before',
                $pathToFixtures . '/removed-interface/source-code-after',
                [
                    'Interface (PATCH)',
                    'Test\Vcs\TestAInterface | Interface was removed. | V033'
                ],
                'Patch change is detected.'
            ],
            'non-api-interface-removed-method' => [
                $pathToFixtures . '/removed-method/source-code-before',
                $pathToFixtures . '/removed-method/source-code-after',
                [
                    'Interface (PATCH)',
                    'Test\Vcs\TestInterface::testMethod | [public] Method has been removed. | V035'
                ],
                'Patch change is detected.'
            ],
            'non-api-interface-removed-last-required-method-parameter' => [
                $pathToFixtures . '/removed-last-required-method-parameter/source-code-before',
                $pathToFixtures . '/removed-last-required-method-parameter/source-code-after',
                [
                    'Interface (PATCH)',
                    'Test\Vcs\TestInterface::testMethodA | [public] Removed last method parameter(s). | M100'
                ],
                'Patch change is detected.'
            ],
            'non-api-interface-renamed-method-parameter' => [
                $pathToFixtures . '/renamed-method-parameter/source-code-before',
                $pathToFixtures . '/renamed-method-parameter/source-code-after',
                [
                    'Interface (PATCH)',
                    'Test\Vcs\TestInterface::testMethodA | [public] Method parameter name changed. | V063'
                ],
                'Patch change is detected.'
            ],
            'non-api-interface-new-optional-method-parameter' => [
                $pathToFixtures . '/new-optional-method-parameter/source-code-before',
                $pathToFixtures . '/new-optional-method-parameter/source-code-after',
                [
                    'Interface (PATCH)',
                    'Test\Vcs\TestInterface::testMethodA | [public] Added optional parameter(s). | M102'
                ],
                'Patch change is detected.'
            ],
            'non-api-interface-exception-superclassed' => [
                $pathToFixtures . '/exception-superclassed/source-code-before',
                $pathToFixtures . '/exception-superclassed/source-code-after',
                [
                    'Interface (PATCH)',
                    'Test\Vcs\TestInterface::exceptionSuperclassed | [public] Exception has been superclassed. | M128'
                ],
                'Patch change is detected.'
            ],
            'non-api-interface-exception-subclassed' => [
                $pathToFixtures . '/exception-subclassed/source-code-before',
                $pathToFixtures . '/exception-subclassed/source-code-after',
                [
                    'Interface (PATCH)',
                    'Test\Vcs\TestInterface::exceptionSubclassed | [public] Exception has been subclassed. | M130'
                ],
                'Patch change is detected.'
            ],
            'non-api-interface-exception-superclass-added' => [
                $pathToFixtures . '/exception-superclass-added/source-code-before',
                $pathToFixtures . '/exception-superclass-added/source-code-after',
                [
                    'Interface (PATCH)',
                    'Test\Vcs\TestInterface::exceptionSuperclassAdded | [public] Superclassed Exception has been added. | M132'
                ],
                'Patch change is detected.'
            ],
            'non-api-interface-exception-subclass-added' => [
                $pathToFixtures . '/exception-subclass-added/source-code-before',
                $pathToFixtures . '/exception-subclass-added/source-code-after',
                [
                    'Suggested semantic versioning change: NONE'
                ],
                'Patch change is detected.'
            ],
            'docblock-return-type-not-changed' => [
                $pathToFixtures . '/docblock-return-type-not-changed/source-code-before',
                $pathToFixtures . '/docblock-return-type-not-changed/source-code-after',
                [
                    'Suggested semantic versioning change: NONE'
                ],
                'Patch change is detected.'
            ],
        ];
    }
}
