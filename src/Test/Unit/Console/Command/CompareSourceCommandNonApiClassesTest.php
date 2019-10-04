<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SemanticVersionChecker\Test\Unit\Console\Command;

use Magento\SemanticVersionChecker\Test\Unit\Console\Command\CompareSourceCommandTest\AbstractTestCase;

/**
 * Test semantic version checker CLI command dealing with non-API classes.
 */
class CompareSourceCommandNonApiClassesTest extends AbstractTestCase
{
    /**
     * Test semantic version checker CLI command for classes that do not have the <kbd>api</kbd> annotation.
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
        $pathToFixtures = __DIR__ . '/CompareSourceCommandTest/_files/non-api-class';
        return [
            'non-api-class-new-class' => [
                $pathToFixtures . '/new-class/source-code-before',
                $pathToFixtures . '/new-class/source-code-after',
                [
                    'Class (PATCH)',
                    'Test\Vcs\TestClass | Class was added. | V014'
                ],
                'Patch change is detected.'
            ],
            'non-api-class-new-method' => [
                $pathToFixtures . '/new-method/source-code-before',
                $pathToFixtures . '/new-method/source-code-after',
                [
                    'Class (PATCH)',
                    'Test\Vcs\TestClass::testMethod | [public] Method has been added. | V015'
                ],
                'Patch change is detected.'
            ],
            'non-api-class-removed-class' => [
                $pathToFixtures . '/removed-class/source-code-before',
                $pathToFixtures . '/removed-class/source-code-after',
                [
                    'Class (PATCH)',
                    'Test\Vcs\TestClass | Class was removed. | V005'
                ],
                'Patch change is detected.'
            ],
            'non-api-class-removed-method' => [
                $pathToFixtures . '/removed-method/source-code-before',
                $pathToFixtures . '/removed-method/source-code-after',
                [
                    'Class (PATCH)',
                    'Test\Vcs\TestClass::testMethod | [public] Method has been removed. | V006'
                ],
                'Patch change is detected.'
            ],
            'non-api-class-new-required-method-parameter' => [
                $pathToFixtures . '/new-required-method-parameter/source-code-before',
                $pathToFixtures . '/new-required-method-parameter/source-code-after',
                [
                    'Class (PATCH)',
                    'Test\Vcs\TestClass::testMethod | [public] Method parameter added. | V010'
                ],
                'Patch change is detected.'
            ],
            'non-api-class-removed-optional-non-last-method-parameter' => [
                $pathToFixtures . '/removed-optional-non-last-method-parameter/source-code-before',
                $pathToFixtures . '/removed-optional-non-last-method-parameter/source-code-after',
                [
                    'Class (PATCH)',
                    'Test\Vcs\TestClass::testMethod | [public] Method parameter name changed. | V060'
                ],
                'Patch change is detected.'
            ],
            'non-api-class-removed-required-method-parameter-followed-by-optional-one' => [
                $pathToFixtures . '/removed-required-method-parameter-followed-by-optional-one/source-code-before',
                $pathToFixtures . '/removed-required-method-parameter-followed-by-optional-one/source-code-after',
                [
                    'Class (PATCH)',
                    'Test\Vcs\TestClass::testMethod | [public] Method parameter name changed. | V060'
                ],
                'Patch change is detected.'
            ],
            'non-api-class-removed-required-non-last-method-parameter' => [
                $pathToFixtures . '/removed-required-non-last-method-parameter/source-code-before',
                $pathToFixtures . '/removed-required-non-last-method-parameter/source-code-after',
                [
                    'Class (PATCH)',
                    'Test\Vcs\TestClass::testMethod | [public] Method parameter removed. | V082'
                ],
                'Patch change is detected.'
            ],
            'non-api-class-new-required-constructor-parameter' => [
                $pathToFixtures . '/new-required-constructor-parameter/source-code-before',
                $pathToFixtures . '/new-required-constructor-parameter/source-code-after',
                [
                    'Class (PATCH)',
                    'Test\Vcs\TestClass::__construct | [public] Added a required constructor object parameter. | M103'
                ],
                'Patch change is detected.'
            ],
            'non-api-class-new-required-scalar-constructor-parameter' => [
                $pathToFixtures . '/new-required-scalar-constructor-parameter/source-code-before',
                $pathToFixtures . '/new-required-scalar-constructor-parameter/source-code-after',
                [
                    'Class (PATCH)',
                    'Test\Vcs\TestClass::__construct | [public] Method parameter added. | V010'
                ],
                'Patch change is detected.'
            ],
            'non-api-class-new-optional-constructor-parameter' => [
                $pathToFixtures . '/new-optional-constructor-parameter/source-code-before',
                $pathToFixtures . '/new-optional-constructor-parameter/source-code-after',
                [
                    'Class (PATCH)',
                    'Test\Vcs\TestClass::__construct | [public] Added an optional constructor parameter. | M112'
                ],
                'Patch change is detected.'
            ],
            'non-api-class-removed-non-last-constructor-parameter' => [
                $pathToFixtures . '/removed-non-last-constructor-parameter/source-code-before',
                $pathToFixtures . '/removed-non-last-constructor-parameter/source-code-after',
                [
                    'Class (PATCH)',
                    'Test\Vcs\TestClass::__construct | [public] Method parameter removed. | V082'
                ],
                'Patch change is detected.'
            ],
            'non-api-class-removed-last-constructor-parameter' => [
                $pathToFixtures . '/removed-last-constructor-parameter/source-code-before',
                $pathToFixtures . '/removed-last-constructor-parameter/source-code-after',
                [
                    'Class (PATCH)',
                    'Test\Vcs\TestClass::__construct | [public] Removed last constructor parameter(s). | M101'
                ],
                'Patch change is detected.'
            ],
            'non-api-class-removed-required-last-method-parameter' => [
                $pathToFixtures . '/removed-required-last-method-parameter/source-code-before',
                $pathToFixtures . '/removed-required-last-method-parameter/source-code-after',
                [
                    'Class (PATCH)',
                    'Test\Vcs\TestClass::testMethod | [public] Removed last method parameter(s). | M100'
                ],
                'Patch change is detected.'
            ],
            'non-api-class-exception-superclassed' => [
                $pathToFixtures . '/exception-superclassed/source-code-before',
                $pathToFixtures . '/exception-superclassed/source-code-after',
                [
                    'Class (PATCH)',
                    'Test\Vcs\TestClass::exceptionSuperclassed | [public] Exception has been superclassed. | M127'
                ],
                'Patch change is detected.'
            ],
            'non-api-class-exception-subclassed' => [
                $pathToFixtures . '/exception-subclassed/source-code-before',
                $pathToFixtures . '/exception-subclassed/source-code-after',
                [
                    'Class (PATCH)',
                    'Test\Vcs\TestClass::exceptionSubclassed | [public] Exception has been subclassed. | M129'
                ],
                'Patch change is detected.'
            ],
            'non-api-class-exception-superclass-added' => [
                $pathToFixtures . '/exception-superclass-added/source-code-before',
                $pathToFixtures . '/exception-superclass-added/source-code-after',
                [
                    'Class (PATCH)',
                    'Test\Vcs\TestClass::exceptionSuperclassAdded | [public] Superclassed Exception has been added. | M131'
                ],
                'Patch change is detected.'
            ],
            'non-api-class-exception-subclass-added' => [
                $pathToFixtures . '/exception-subclass-added/source-code-before',
                $pathToFixtures . '/exception-subclass-added/source-code-after',
                [
                    'Suggested semantic versioning change: NONE'
                ],
                'Patch change is detected.'
            ],
        ];
    }
}
