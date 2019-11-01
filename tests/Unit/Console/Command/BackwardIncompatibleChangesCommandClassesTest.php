<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SemanticVersionChecker\Test\Unit\Console\Command;

use Magento\SemanticVersionChecker\Test\Unit\Console\Command\BackwardIncompatibleChangesCommandTest\AbstractTestCase;

/**
 * Contains unit test cases for
 * {@link \Magento\SemanticVersionChecker\Console\Command\BackwardIncompatibleChangesCommand} dealing with changes
 * on classes.
 */
class BackwardIncompatibleChangesCommandClassesTest extends AbstractTestCase
{
    /**
     * Test backward incompatible changes CLI command for changes on classes.
     *
     * @param string $pathToSourceCodeBefore
     * @param string $pathToSourceCodeAfter
     * @param array $expectedBreakingChanges
     * @param array $expectedMembershipChanges
     * @param bool $shouldSkipTest
     * @dataProvider changesDataProvider
     * @return void
     * @throws \Exception
     */
    public function testExecute(
        $pathToSourceCodeBefore,
        $pathToSourceCodeAfter,
        $expectedBreakingChanges,
        $expectedMembershipChanges,
        $shouldSkipTest = false
    ) {
        $this->doTestExecute(
            $pathToSourceCodeBefore,
            $pathToSourceCodeAfter,
            $expectedBreakingChanges,
            $expectedMembershipChanges,
            $shouldSkipTest
        );
    }

    public function changesDataProvider()
    {
        $pathToFixtures = __DIR__ . '/BackwardIncompatibleChangesCommandTest/_files/class';
        return [
            'class-new-class' => [
                $pathToFixtures . '/new-class/source-code-before',
                $pathToFixtures . '/new-class/source-code-after',
                [
                    'class' => ['Test\Vcs\TestNewClass|Class was added.']
                ],
                [
                    'class' => ['Test\Vcs\TestMembershipClass|Class was added.']
                ]
            ],
            'class-new-method' => [
                $pathToFixtures . '/new-method/source-code-before',
                $pathToFixtures . '/new-method/source-code-after',
                [
                    'class' => ['Test\Vcs\TestClass::testNewMethod|[public] Method has been added.']
                ],
                [
                    'class' => ['Test\Vcs\TestClass::testMembershipMethod|[public] Method has been added.']
                ]
            ],
            'class-removed-class' => [
                $pathToFixtures . '/removed-class/source-code-before',
                $pathToFixtures . '/removed-class/source-code-after',
                [
                    'class' => ['Test\Vcs\TestRemoveClass|Class was removed.']
                ],
                [
                    'class' => ['Test\Vcs\TestMembershipClass|Class was removed.']
                ]
            ],
            'class-removed-method' => [
                $pathToFixtures . '/removed-method/source-code-before',
                $pathToFixtures . '/removed-method/source-code-after',
                [
                    'class' => ['Test\Vcs\TestClass::testRemoveMethod|[public] Method has been removed.']
                ],
                [
                    'class' => ['Test\Vcs\TestClass::testMembershipMethod|[public] Method has been removed.']
                ]
            ]
        ];
    }
}
