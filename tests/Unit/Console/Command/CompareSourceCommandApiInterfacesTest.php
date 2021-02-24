<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Console\Command;

use Magento\SemanticVersionChecker\Test\Unit\Console\Command\CompareSourceCommandTest\AbstractTestCase;

/**
 * Test semantic version checker CLI command dealing with API interfaces.
 */
class CompareSourceCommandApiInterfacesTest extends AbstractTestCase
{
    /**
     * Test semantic version checker CLI command for changes on interfaces that have the <kbd>api</kbd> annotation.
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
        $pathToFixtures = __DIR__ . '/CompareSourceCommandTest/_files/api-interface';
        return [
            'api-interface-new-interface' => [
                $pathToFixtures . '/new-interface/source-code-before',
                $pathToFixtures . '/new-interface/source-code-after',
                [
                    'Interface (MINOR)',
                    'Test\Vcs\TestAInterface | Interface was added. | V032'
                ],
                'Minor change is detected.'
            ],
            'api-interface-new-method' => [
                $pathToFixtures . '/new-method/source-code-before',
                $pathToFixtures . '/new-method/source-code-after',
                [
                    'Interface (MINOR)',
                    'Test\Vcs\TestInterface::testMethod | [public] Method has been added. | V034'
                ],
                'Minor change is detected.'
            ],
            'api-interface-new-required-method-parameter' => [
                $pathToFixtures . '/new-required-method-parameter/source-code-before',
                $pathToFixtures . '/new-required-method-parameter/source-code-after',
                [
                    'Interface (MAJOR)',
                    'Test\Vcs\TestInterface::testMethodA | [public] Method parameter added. | V036'
                ],
                'Major change is detected.'
            ],
            'api-interface-removed-interface' => [
                $pathToFixtures . '/removed-interface/source-code-before',
                $pathToFixtures . '/removed-interface/source-code-after',
                [
                    'Interface (MAJOR)',
                    'Test\Vcs\TestAInterface | Interface was removed. | V033'
                ],
                'Major change is detected.'
            ],
            'api-interface-removed-method' => [
                $pathToFixtures . '/removed-method/source-code-before',
                $pathToFixtures . '/removed-method/source-code-after',
                [
                    'Interface (MAJOR)',
                    'Test\Vcs\TestInterface::testMethod | [public] Method has been removed. | V035'
                ],
                'Major change is detected.'
            ],
            'api-interface-removed-last-required-method-parameter' => [
                $pathToFixtures . '/removed-last-required-method-parameter/source-code-before',
                $pathToFixtures . '/removed-last-required-method-parameter/source-code-after',
                [
                    'Interface (MINOR)',
                    'Test\Vcs\TestInterface::testMethodA | [public] Removed last method parameter(s). | M100'
                ],
                'Minor change is detected.'
            ],
            'api-interface-renamed-method-parameter' => [
                $pathToFixtures . '/renamed-method-parameter/source-code-before',
                $pathToFixtures . '/renamed-method-parameter/source-code-after',
                [
                    'Interface (MAJOR)',
                    'Test\Vcs\TestInterface::testMethodA | [public] Method parameter name changed. | V063'
                ],
                'Major change is detected.'
            ],
            'api-interface-new-optional-method-parameter' => [
                $pathToFixtures . '/new-optional-method-parameter/source-code-before',
                $pathToFixtures . '/new-optional-method-parameter/source-code-after',
                [
                    'Interface (MAJOR)',
                    'Test\Vcs\TestInterface::testMethodA | [public] Added optional parameter(s). | M102'
                ],
                'Major change is detected.'
            ],
            'api-interface-remove-extends' => [
                $pathToFixtures . '/remove-extends/source-code-before',
                $pathToFixtures . '/remove-extends/source-code-after',
                [
                    'Interface (MAJOR)',
                    'Test\Vcs\TestInterface | Extends has been removed. | M0122'
                ],
                'Major change is detected.',
            ],
            'api-interface-added-extends' => [
                $pathToFixtures . '/added-extends/source-code-before',
                $pathToFixtures . '/added-extends/source-code-after',
                [
                    'Interface (MINOR)',
                    'Test\Vcs\TestInterface1 | Added parent to interface. | M0127',
                    'Test\Vcs\TestInterface2 | Added parent to interface. | M0127'
                ],
                'Minor change is detected.'
            ],
            'api-interface-exception-superclassed' => [
                $pathToFixtures . '/exception-superclassed/source-code-before',
                $pathToFixtures . '/exception-superclassed/source-code-after',
                [
                    'Interface (MAJOR)',
                    'Test\Vcs\TestInterface::exceptionSuperclassed | [public] Exception has been superclassed. | M128'
                ],
                'Major change is detected.'
            ],
            'api-interface-exception-subclassed' => [
                $pathToFixtures . '/exception-subclassed/source-code-before',
                $pathToFixtures . '/exception-subclassed/source-code-after',
                [
                    'Interface (MINOR)',
                    'Test\Vcs\TestInterface::exceptionSubclassed | [public] Exception has been subclassed. | M130'
                ],
                'Minor change is detected.'
            ],
            'api-interface-exception-superclass-added' => [
                $pathToFixtures . '/exception-superclass-added/source-code-before',
                $pathToFixtures . '/exception-superclass-added/source-code-after',
                [
                    'Interface (MAJOR)',
                    'Test\Vcs\TestInterface::exceptionSuperclassAdded | [public] Superclassed Exception has been added. | M132'
                ],
                'Major change is detected.'
            ],
            'api-interface-exception-subclass-added' => [
                $pathToFixtures . '/exception-subclass-added/source-code-before',
                $pathToFixtures . '/exception-subclass-added/source-code-after',
                [
                    'Suggested semantic versioning change: NONE'
                ],
                'Patch change is detected.'
            ],
            'api-moved-method-parameter-type-from-docblock-to-inline' => [
                $pathToFixtures . '/moved-method-parameter-type-from-docblock-to-inline/source-code-before',
                $pathToFixtures . '/moved-method-parameter-type-from-docblock-to-inline/source-code-after',
                [
                    'Interface (MAJOR)',
                    'Test\Vcs\TestInterface::movedNativeType    | [public] Method parameter typehint was moved from doc block annotation to in-line. | M138',
                    'Test\Vcs\TestInterface::movedNonNativeType | [public] Method parameter typehint was moved from doc block annotation to in-line. | M138',
                ],
                'Major change is detected.'
            ],
            'api-moved-method-parameter-type-from-inline-to-docblock' => [
                $pathToFixtures . '/moved-method-parameter-type-from-inline-to-docblock/source-code-before',
                $pathToFixtures . '/moved-method-parameter-type-from-inline-to-docblock/source-code-after',
                [
                    'Interface (MAJOR)',
                    'Test\Vcs\TestInterface::movedNativeType    | [public] Method parameter typehint was moved from in-line to doc block annotation. | M139',
                    'Test\Vcs\TestInterface::movedNonNativeType | [public] Method parameter typehint was moved from in-line to doc block annotation. | M139',
                ],
                'Major change is detected.'
            ],
            'api-moved-method-return-type-from-docblock-to-inline' => [
                $pathToFixtures . '/moved-method-return-type-from-docblock-to-inline/source-code-before',
                $pathToFixtures . '/moved-method-return-type-from-docblock-to-inline/source-code-after',
                [
                    'Interface (MAJOR)',
                    'Test\Vcs\TestInterface::movedNativeType    | [public] Method return typehint was moved from doc block annotation to in-line. | M140',
                    'Test\Vcs\TestInterface::movedNonNativeType | [public] Method return typehint was moved from doc block annotation to in-line. | M140',
                ],
                'Major change is detected.'
            ],
            'api-moved-method-return-type-from-inline-to-docblock' => [
                $pathToFixtures . '/moved-method-return-type-from-inline-to-docblock/source-code-before',
                $pathToFixtures . '/moved-method-return-type-from-inline-to-docblock/source-code-after',
                [
                    'Interface (MAJOR)',
                    'Test\Vcs\TestInterface::movedNativeType    | [public] Method return typehint was moved from in-line to doc block annotation. | M141',
                    'Test\Vcs\TestInterface::movedNonNativeType | [public] Method return typehint was moved from in-line to doc block annotation. | M141',
                ],
                'Major change is detected.'
            ],
            'api-moved-method-variable-type-from-docblock-to-inline' => [
                $pathToFixtures . '/moved-method-variable-type-from-docblock-to-inline/source-code-before',
                $pathToFixtures . '/moved-method-variable-type-from-docblock-to-inline/source-code-after',
                [
                    'Interface (MAJOR)',
                    'Test\Vcs\TestInterface::movedNativeType    | [public] Method variable typehint was moved from doc block annotation to in-line. | M147',
                    'Test\Vcs\TestInterface::movedNonNativeType | [public] Method variable typehint was moved from doc block annotation to in-line. | M147',
                ],
                'Major change is detected.'
            ],
            'api-moved-method-variable-type-from-inline-to-docblock' => [
                $pathToFixtures . '/moved-method-variable-type-from-inline-to-docblock/source-code-before',
                $pathToFixtures . '/moved-method-variable-type-from-inline-to-docblock/source-code-after',
                [
                    'Interface (MAJOR)',
                    'Test\Vcs\TestInterface::movedNativeType    | [public] Method variable typehint was moved from in-line to doc block annotation. | M150',
                    'Test\Vcs\TestInterface::movedNonNativeType | [public] Method variable typehint was moved from in-line to doc block annotation. | M150',
                ],
                'Major change is detected.'
            ],
            'api-annotation-added-to-interface' => [
                $pathToFixtures . '/annotation-added/source-code-before',
                $pathToFixtures . '/annotation-added/source-code-after',
                [
                    'Interface (MINOR)',
                    'Test\Vcs\TestInterface | @api annotation has been added. | M0141',
                ],
                'Minor change is detected.',
            ],
            'api-annotation-removed-from-interface' => [
                $pathToFixtures . '/annotation-removed/source-code-before',
                $pathToFixtures . '/annotation-removed/source-code-after',
                [
                    'Interface (MAJOR)',
                    'Test\Vcs\TestInterface | @api annotation has been removed. | M0142',
                ],
                'Major change is detected.',
            ],
            'api-annotation-not-changed' => [
                $pathToFixtures . '/annotation-not-changed/source-code-before',
                $pathToFixtures . '/annotation-not-changed/source-code-after',
                [
                    'Suggested semantic versioning change: NONE',
                ],
                'Patch change is detected.',
            ],
        ];
    }
}
