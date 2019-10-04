<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Test\Unit\Console\Command;

use Magento\SemanticVersionChecker\Test\Unit\Console\Command\CompareSourceCommandTest\AbstractTestCase;

/**
 * Test semantic version checker CLI command dealing with API traits.
 */
class CompareSourceCommandApiTraitsTest extends AbstractTestCase
{
    /**
     * Test semantic version checker CLI command for traits that have the <kbd>api</kbd> annotation.
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
        $pathToFixtures = __DIR__ . '/CompareSourceCommandTest/_files/api-trait';

        return [
            //prove that unchanged traits will not trigger false positives
            'api-trait-no-change' => [
                $pathToFixtures . '/no-change/source-code-before',
                $pathToFixtures . '/no-change/source-code-after',
                [
                    'Suggested semantic versioning change: NONE',
                    'No changed files found.',
                ],
                'None change is detected.',
            ],
            //prove that changed methods in traits trigger expected levels
            'api-trait-new-method' => [
                $pathToFixtures . '/new-method/source-code-before',
                $pathToFixtures . '/new-method/source-code-after',
                [
                    'Trait (MINOR)',
                    'Test\Vcs\TestTrait::testMethod | [private] Method has been added. | V057'
                ],
                'Minor change is detected.'
            ],
            'api-trait-removed-method' => [
                $pathToFixtures . '/removed-method/source-code-before',
                $pathToFixtures . '/removed-method/source-code-after',
                [
                    'Trait (MAJOR)',
                    'Test\Vcs\TestTrait::testMethod | [private] Method has been removed. | V058'
                ],
                'Major change is detected.'
            ],
            'api-trait-new-required-method-parameter' => [
                $pathToFixtures . '/new-required-method-parameter/source-code-before',
                $pathToFixtures . '/new-required-method-parameter/source-code-after',
                [
                    'Trait (MAJOR)',
                    'Test\Vcs\TestTrait::testMethod | [private] Method parameter added. | V059'
                ],
                'Major change is detected.'
            ],
            'api-trait-new-optional-method-parameter' => [
                $pathToFixtures . '/new-optional-method-parameter/source-code-before',
                $pathToFixtures . '/new-optional-method-parameter/source-code-after',
                [
                    'Trait (MINOR)',
                    'Test\Vcs\TestTrait::publicMethod    | [public] Added optional parameter(s).    | M102',
                    'Test\Vcs\TestTrait::protectedMethod | [protected] Added optional parameter(s). | M102',
                    'Test\Vcs\TestTrait::privateMethod   | [private] Added optional parameter(s).   | M102',
                ],
                'Minor change is detected.'
            ],
            'api-trait-removed-optional-non-last-method-parameter' => [
                $pathToFixtures . '/removed-optional-non-last-method-parameter/source-code-before',
                $pathToFixtures . '/removed-optional-non-last-method-parameter/source-code-after',
                [
                    'Trait (MAJOR)',
                    'Test\Vcs\TestTrait::testMethod | [private] Method parameter name changed. | V066'
                ],
                'Major change is detected.'
            ],
            'api-trait-removed-required-method-parameter-followed-by-optional-one' => [
                $pathToFixtures . '/removed-required-method-parameter-followed-by-optional-one/source-code-before',
                $pathToFixtures . '/removed-required-method-parameter-followed-by-optional-one/source-code-after',
                [
                    'Trait (MAJOR)',
                    'Test\Vcs\TestTrait::testMethod | [private] Method parameter name changed. | V066'
                ],
                'Major change is detected.'
            ],
            'api-trait-removed-required-non-last-method-parameter' => [
                $pathToFixtures . '/removed-required-non-last-method-parameter/source-code-before',
                $pathToFixtures . '/removed-required-non-last-method-parameter/source-code-after',
                [
                    'Trait (MAJOR)',
                    'Test\Vcs\TestTrait::testMethod | [private] Method parameter removed. | V102'
                ],
                'Major change is detected.'
            ],
            'api-trait-removed-required-last-method-parameter' => [
                $pathToFixtures . '/removed-required-last-method-parameter/source-code-before',
                $pathToFixtures . '/removed-required-last-method-parameter/source-code-after',
                [
                    'Trait (MINOR)',
                    'Test\Vcs\TestTrait::publicMethod    | [public] Removed last method parameter(s).    | M100',
                    'Test\Vcs\TestTrait::protectedMethod | [protected] Removed last method parameter(s). | M100',
                    'Test\Vcs\TestTrait::privateMethod   | [private] Removed last method parameter(s).   | M100',
                ],
                'Minor change is detected.'
            ],
            'api-trait-changed-method-parameter-type' => [
                $pathToFixtures . '/changed-method-parameter-type/source-code-before',
                $pathToFixtures . '/changed-method-parameter-type/source-code-after',
                [
                    'Trait (MAJOR)',
                    'Test\Vcs\TestTrait::testMethod | [private] Method parameter typing changed. | M119'
                ],
                'Major change is detected.'
            ],
            'api-trait-changed-method-return-type' => [
                $pathToFixtures.'/changed-method-return-type/source-code-before',
                $pathToFixtures.'/changed-method-return-type/source-code-after',
                [
                    'Trait (MAJOR)',
                    'Test\Vcs\TestTrait::declarationAddedPublic         | [public] Method return typing changed.    | M124',
                    'Test\Vcs\TestTrait::declarationAddedProtected      | [protected] Method return typing changed. | M125',
                    'Test\Vcs\TestTrait::declarationChangedPublic       | [public] Method return typing changed.    | M124',
                    'Test\Vcs\TestTrait::annotationChangedPublic        | [public] Method return typing changed.    | M124',
                    'Test\Vcs\TestTrait::declarationChangedProtected    | [protected] Method return typing changed. | M125',
                    'Test\Vcs\TestTrait::annotationChangedProtected     | [protected] Method return typing changed. | M125',
                    'Test\Vcs\TestTrait::declarationRemovedPublic       | [public] Method return typing changed.    | M124',
                    'Test\Vcs\TestTrait::annotationRemovedPublic        | [public] Method return typing changed.    | M124',
                    'Test\Vcs\TestTrait::declarationRemovedProtected    | [protected] Method return typing changed. | M125',
                    'Test\Vcs\TestTrait::annotationRemovedProtected     | [protected] Method return typing changed. | M125',
                    'Test\Vcs\TestTrait::php7RemoveAnnotationWithoutDoc | [public] Method return typing changed.    | M124',
                    'Test\Vcs\TestTrait::declarationAddedPrivate        | [private] Method return typing changed.   | M126',
                    'Test\Vcs\TestTrait::declarationChangedPrivate      | [private] Method return typing changed.   | M126',
                    'Test\Vcs\TestTrait::annotationChangedPrivate       | [private] Method return typing changed.   | M126',
                    'Test\Vcs\TestTrait::declarationRemovedPrivate      | [private] Method return typing changed.   | M126',
                    'Test\Vcs\TestTrait::annotationRemovedPrivate       | [private] Method return typing changed.   | M126',
                    'Test\Vcs\TestTrait::annotationAddedPublic          | [public] Method return typing changed.    | M124',
                    'Test\Vcs\TestTrait::annotationAddedProtected       | [protected] Method return typing changed. | M125',
                    'Test\Vcs\TestTrait::annotationAddedPrivate         | [private] Method return typing changed.   | M126',
                ],
                'Major change is detected.',
            ],
            'api-trait-new-method-parameter-type' => [
                $pathToFixtures . '/new-method-parameter-type/source-code-before',
                $pathToFixtures . '/new-method-parameter-type/source-code-after',
                [
                    'Trait (MAJOR)',
                    'Test\Vcs\TestTrait::testMethod | [private] Method parameter typing added. | V105'
                ],
                'Major change is detected.'
            ],
            'api-trait-removed-method-parameter-type' => [
                $pathToFixtures . '/removed-method-parameter-type/source-code-before',
                $pathToFixtures . '/removed-method-parameter-type/source-code-after',
                [
                    'Trait (MAJOR)',
                    'Test\Vcs\TestTrait::testMethod | [private] Method parameter typing removed. | V108'
                ],
                'Major change is detected.'
            ],
            'api-trait-private-method-changed' => [
                $pathToFixtures . '/private-method-changed/source-code-before',
                $pathToFixtures . '/private-method-changed/source-code-after',
                [
                    'Trait (PATCH)',
                    'Test\Vcs\TestTrait::methodChanged | [private] Method implementation changed. | V054',
                ],
                'Patch change is detected.',
            ],
        ];
    }
}
