<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\SemanticVersionChecker\Test\Unit\Console\Command;

use Magento\Tools\SemanticVersionChecker\Test\Unit\Console\Command\BackwardIncompatibleChangesCommandTest\AbstractTestCase;

/**
 * Contains unit test cases for
 * {@link \Magento\Tools\SemanticVersionChecker\Console\Command\BackwardIncompatibleChangesCommand} dealing with changes
 * on interfaces.
 */
class BackwardIncompatibleChangesCommandInterfacesTest extends AbstractTestCase
{
    /**
     * Test backward incompatible changes CLI command for changes on interfaces.
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
        $pathToFixtures = __DIR__ . '/BackwardIncompatibleChangesCommandTest/_files/interface';

        return [
            'interface-new-interface' => [
                $pathToFixtures . '/new-interface/source-code-before',
                $pathToFixtures . '/new-interface/source-code-after',
                [
                    'interface' => ['Test\Vcs\TestNewInterface|Interface was added.']
                ],
                [
                    'interface' => ['Test\Vcs\TestMembershipInterface|Interface was added.']
                ]
            ],
            'interface-new-method' => [
                $pathToFixtures . '/new-method/source-code-before',
                $pathToFixtures . '/new-method/source-code-after',
                [
                    'interface' => ['Test\Vcs\TestInterface::testNewMethod|[public] Method has been added.']
                ],
                [
                    'interface' => ['Test\Vcs\TestInterface::testMembershipMethod|[public] Method has been added.']
                ]
            ],
            'interface-removed-interface' => [
                $pathToFixtures . '/removed-interface/source-code-before',
                $pathToFixtures . '/removed-interface/source-code-after',
                [
                    'interface' => ['Test\Vcs\TestRemoveInterface|Interface was removed.']
                ],
                [
                    'interface' => ['Test\Vcs\TestMembershipInterface|Interface was removed.']
                ]
            ],
            'interface-removed-method' => [
                $pathToFixtures . '/removed-method/source-code-before',
                $pathToFixtures . '/removed-method/source-code-after',
                [
                    'interface' => ['Test\Vcs\TestInterface::testRemoveMethod|[public] Method has been removed.']
                ],
                [
                    'interface' => ['Test\Vcs\TestInterface::testMembershipMethod|[public] Method has been removed.']
                ]
            ],
            'interface-optional-parameter' => [
                $pathToFixtures . '/new-optional-parameter/source-code-before',
                $pathToFixtures . '/new-optional-parameter/source-code-after',
                [
                    'interface' => ['Test\Vcs\TestInterface::testParameterAdded|[public] Added optional parameter(s).']
                ],
                [
                ]
            ]
        ];
    }
}
