<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Console\Command;

use Magento\SemanticVersionChecker\Console\Command\BackwardIncompatibleChangesCommand;
use Magento\SemanticVersionChecker\Reporter\BreakingChangeTableReporter;
use Symfony\Component\Console\Tester\CommandTester;

class BackwardIncompatibleChangesCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var BackwardIncompatibleChangesCommand
     */
    private $command;

    /**
     * @var string
     */
    private $bicFilePath;

    protected function setUp()
    {
        $this->command = new BackwardIncompatibleChangesCommand();
        $this->bicFilePath = TESTS_TEMP_DIR . '/bic-' . time() . '.html';
    }

    protected function tearDown()
    {
        parent::tearDown();
        unlink($this->bicFilePath);
    }

    /**
     * Test backward incompatible changes CLI command.
     *
     * 1. Run backward incompatible changes command to compare 2 source code directories
     * 2. Assert that report contains expected entries
     * 3. Assert return code
     *
     * @param string $pathToSourceCodeBefore
     * @param string $pathToSourceCodeAfter
     * @param array $expectedBreakingChanges
     * @param array $expectedMembershipChanges
     * @param bool $shouldSkipTest
     * @dataProvider executeDataProvider
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
        try {
            $commandTester = new CommandTester($this->command);
            $commandTester->execute(
                [
                    'source-before' => $pathToSourceCodeBefore,
                    'source-after' => $pathToSourceCodeAfter,
                    'target-file' => $this->bicFilePath,
                    '--include-patterns' => __DIR__ . '/BackwardIncompatibleChangesCommandTest/_files/application_includes.txt'
                ]
            );
            $actualReport = file_get_contents($this->bicFilePath);
            $this->assertExpectedChanges($expectedBreakingChanges, $actualReport, 'breaking-change');
            $this->assertExpectedChanges($expectedMembershipChanges, $actualReport, 'api-membership');
            $this->assertEquals(0, $commandTester->getStatusCode());
        } catch (\Exception $e) {
            if ($shouldSkipTest) {
                $this->markTestSkipped($e->getMessage());
            } else {
                throw $e;
            }
        }
    }

    public function executeDataProvider()
    {
        return array_merge(
            $this->apiInterfaceChangesDataProvider(),
            $this->apiClassChangesDataProvider()
        );
    }

    private function apiInterfaceChangesDataProvider()
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
            ]
        ];
    }

    private function apiClassChangesDataProvider()
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

    /**
     * Assert that the number of table rows is as expected and all changes are present in the table
     *
     * @param array $expectedChanges
     * @param string $actualReport
     * @param string $reportType
     * @return void
     */
    private function assertExpectedChanges($expectedChanges, $actualReport, $reportType)
    {
        foreach ($expectedChanges as $context => $expectedArr) {
            $header = BreakingChangeTableReporter::formatSectionHeader($this->bicFilePath, $context, $reportType);
            $this->assertContains($header, $actualReport);

            $sectionStart = strpos($actualReport, $header);
            $sectionLen = strpos($actualReport, '</table>') + 8 - $sectionStart;
            $actualSection = substr($actualReport, $sectionStart, $sectionLen);

            $actualCount = substr_count($actualSection, '<tr>') - 1;
            $this->assertEquals(count($expectedArr), $actualCount);

            foreach ($expectedArr as $expectedChange) {
                $expectedSegments = explode('|', $expectedChange);
                $expected = '<td>' . $expectedSegments[0] . "</td>\n        <td>" . $expectedSegments[1] . '</td>';
                $this->assertContains($expected, $actualSection);
            }
        }
    }
}
