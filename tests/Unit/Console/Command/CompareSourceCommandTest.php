<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Console\Command;

use Magento\SemanticVersionChecker\Console\Command\CompareSourceCommand;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Test semantic version checker CLI command.
 */
class CompareSourceCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CompareSourceCommand
     */
    private $command;

    /**
     * @var string
     */
    private $svcLogPath;

    protected function setUp()
    {
        $this->command = new CompareSourceCommand();
        $this->svcLogPath = TESTS_TEMP_DIR . '/svc-' . time() . '.log';
    }

    protected function tearDown()
    {
        parent::tearDown();
        unlink($this->svcLogPath);
    }

    /**
     * Test semantic version checker CLI command.
     *
     * 1. Run semantic version checker command to compare 2 source code directories
     * 2. Assert that SVC log contains expected entries
     * 3. Assert console output
     * 4. Assert return code
     *
     * @param string $pathToSourceCodeBefore
     * @param string $pathToSourceCodeAfter
     * @param string[] $expectedLogEntries
     * @param string $expectedOutput
     * @param bool $shouldSkipTest
     * @dataProvider executeDataProvider
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
        try {
            $commandTester = new CommandTester($this->command);
            $commandTester->execute(
                [
                    'source-before' => $pathToSourceCodeBefore,
                    'source-after' => $pathToSourceCodeAfter,
                    '--log-output-location' => $this->svcLogPath,
                    '--include-patterns' => __DIR__ . '/CompareSourceCommandTest/_files/application_includes.txt',
                ]
            );
            $actualSvcLogContents = file_get_contents($this->svcLogPath);
            foreach ($expectedLogEntries as $expectedLogEntry) {
                $this->assertContains($expectedLogEntry, $actualSvcLogContents);
            }
            $this->assertContains($expectedOutput, $commandTester->getDisplay());
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
            $this->nonApiInterfaceChangesDataProvider(),
            $this->apiClassChangesDataProvider(),
            $this->nonApiClassChangesDataProvider()
        );
    }

    private function apiInterfaceChangesDataProvider()
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
                    'Interface (MINOR)',
                    'Test\Vcs\TestInterface::testMethodA | [public] Added optional parameter(s). | M102'
                ],
                'Minor change is detected.'
            ]
        ];
    }

    private function nonApiInterfaceChangesDataProvider()
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
            ]
        ];
    }

    private function apiClassChangesDataProvider()
    {
        $pathToFixtures = __DIR__ . '/CompareSourceCommandTest/_files/api-class';
        return [
            'api-class-new-class' => [
                $pathToFixtures . '/new-class/source-code-before',
                $pathToFixtures . '/new-class/source-code-after',
                [
                    'Class (MINOR)',
                    'Test\Vcs\TestClass | Class was added. | V014'
                ],
                'Minor change is detected.'
            ],
            'api-class-new-method' => [
                $pathToFixtures . '/new-method/source-code-before',
                $pathToFixtures . '/new-method/source-code-after',
                [
                    'Class (MINOR)',
                    'Test\Vcs\TestClass::testMethod | [public] Method has been added. | V015'
                ],
                'Minor change is detected.'
            ],
            'api-class-removed-class' => [
                $pathToFixtures . '/removed-class/source-code-before',
                $pathToFixtures . '/removed-class/source-code-after',
                [
                    'Class (MAJOR)',
                    'Test\Vcs\TestClass | Class was removed. | V005'
                ],
                'Major change is detected.'
            ],
            'api-class-removed-method' => [
                $pathToFixtures . '/removed-method/source-code-before',
                $pathToFixtures . '/removed-method/source-code-after',
                [
                    'Class (MAJOR)',
                    'Test\Vcs\TestClass::testMethod | [public] Method has been removed. | V006'
                ],
                'Major change is detected.'
            ],
            'api-class-new-required-method-parameter' => [
                $pathToFixtures . '/new-required-method-parameter/source-code-before',
                $pathToFixtures . '/new-required-method-parameter/source-code-after',
                [
                    'Class (MAJOR)',
                    'Test\Vcs\TestClass::testMethod | [public] Method parameter added. | V010'
                ],
                'Major change is detected.'
            ],
            'api-class-new-optional-method-parameter' => [
                $pathToFixtures . '/new-optional-method-parameter/source-code-before',
                $pathToFixtures . '/new-optional-method-parameter/source-code-after',
                [
                    'Class (MINOR)',
                    'Test\Vcs\TestClass::publicMethod    | [public] Added optional parameter(s).    | M102',
                    'Test\Vcs\TestClass::protectedMethod | [protected] Added optional parameter(s). | M102'
                ],
                'Minor change is detected.'
            ],
            'api-class-removed-optional-non-last-method-parameter' => [
                $pathToFixtures . '/removed-optional-non-last-method-parameter/source-code-before',
                $pathToFixtures . '/removed-optional-non-last-method-parameter/source-code-after',
                [
                    'Class (MAJOR)',
                    'Test\Vcs\TestClass::testMethod | [public] Method parameter name changed. | V060'
                ],
                'Major change is detected.'
            ],
            'api-class-removed-required-method-parameter-followed-by-optional-one' => [
                $pathToFixtures . '/removed-required-method-parameter-followed-by-optional-one/source-code-before',
                $pathToFixtures . '/removed-required-method-parameter-followed-by-optional-one/source-code-after',
                [
                    'Class (MAJOR)',
                    'Test\Vcs\TestClass::testMethod | [public] Method parameter name changed. | V060'
                ],
                'Major change is detected.'
            ],
            'api-class-removed-required-non-last-method-parameter' => [
                $pathToFixtures . '/removed-required-non-last-method-parameter/source-code-before',
                $pathToFixtures . '/removed-required-non-last-method-parameter/source-code-after',
                [
                    'Class (MAJOR)',
                    'Test\Vcs\TestClass::testMethod | [public] Method parameter removed. | V082'
                ],
                'Major change is detected.'
            ],
            'api-class-new-required-constructor-parameter' => [
                $pathToFixtures . '/new-required-constructor-parameter/source-code-before',
                $pathToFixtures . '/new-required-constructor-parameter/source-code-after',
                [
                    'Class (MINOR)',
                    'Test\Vcs\TestClass::__construct | [public] Added a required constructor object parameter. | M103'
                ],
                'Minor change is detected.'
            ],
            'api-class-new-required-constructor-parameter-for-extendable' => [
                $pathToFixtures . '/new-required-constructor-parameter-for-extendable/source-code-before',
                $pathToFixtures . '/new-required-constructor-parameter-for-extendable/source-code-after',
                [
                    'Class (MINOR)',
                    'Magento\Framework\Model\AbstractExtensibleModel::__construct | [public] Added a required constructor object parameter. | M103'
                ],
                'Minor change is detected.'
            ],
            'api-class-new-required-scalar-constructor-parameter-for-extendable' => [
                $pathToFixtures . '/new-required-scalar-constructor-parameter-for-extendable/source-code-before',
                $pathToFixtures . '/new-required-scalar-constructor-parameter-for-extendable/source-code-after',
                [
                    'Class (MAJOR)',
                    'Magento\Framework\Model\AbstractExtensibleModel::__construct | [public] Method parameter added. | V010'
                ],
                'Major change is detected.'
            ],
            'api-class-new-optional-scalar-constructor-parameter-for-extendable' => [
                $pathToFixtures . '/new-optional-scalar-constructor-parameter-for-extendable/source-code-before',
                $pathToFixtures . '/new-optional-scalar-constructor-parameter-for-extendable/source-code-after',
                [
                    'Class (MINOR)',
                    'Magento\Framework\Model\AbstractExtensibleModel::__construct | [public] Added an optional constructor parameter to extendable @api class. | M111'
                ],
                'Minor change is detected.'
            ],
            'api-class-new-optional-constructor-parameter-for-extendable' => [
                $pathToFixtures . '/new-optional-constructor-parameter-for-extendable/source-code-before',
                $pathToFixtures . '/new-optional-constructor-parameter-for-extendable/source-code-after',
                [
                    'Class (MINOR)',
                    'Magento\Framework\Model\AbstractExtensibleModel::__construct | [public] Added an optional constructor parameter to extendable @api class. | M111'
                ],
                'Minor change is detected.'
            ],
            'api-class-new-required-scalar-constructor-parameter' => [
                $pathToFixtures . '/new-required-scalar-constructor-parameter/source-code-before',
                $pathToFixtures . '/new-required-scalar-constructor-parameter/source-code-after',
                [
                    'Class (MAJOR)',
                    'Test\Vcs\TestClass::__construct | [public] Method parameter added. | V010'
                ],
                'Major change is detected.'
            ],
            'api-class-new-optional-constructor-parameter' => [
                $pathToFixtures . '/new-optional-constructor-parameter/source-code-before',
                $pathToFixtures . '/new-optional-constructor-parameter/source-code-after',
                [
                    'Class (PATCH)',
                    'Test\Vcs\TestClass::__construct | [public] Added an optional constructor parameter. | M112'
                ],
                'Patch change is detected.'
            ],
            'api-class-removed-non-last-constructor-parameter' => [
                $pathToFixtures . '/removed-non-last-constructor-parameter/source-code-before',
                $pathToFixtures . '/removed-non-last-constructor-parameter/source-code-after',
                [
                    'Class (MAJOR)',
                    'Test\Vcs\TestClass::__construct | [public] Method parameter removed. | V082'
                ],
                'Major change is detected.'
            ],
            'api-class-removed-last-constructor-parameter' => [
                $pathToFixtures . '/removed-last-constructor-parameter/source-code-before',
                $pathToFixtures . '/removed-last-constructor-parameter/source-code-after',
                [
                    'Class (PATCH)',
                    'Test\Vcs\TestClass::__construct | [public] Removed last constructor parameter(s). | M101'
                ],
                'Patch change is detected.'
            ],
            'api-class-removed-required-last-method-parameter' => [
                $pathToFixtures . '/removed-required-last-method-parameter/source-code-before',
                $pathToFixtures . '/removed-required-last-method-parameter/source-code-after',
                [
                    'Class (MINOR)',
                    'Test\Vcs\TestClass::publicMethod    | [public] Removed last method parameter(s).    | M100',
                    'Test\Vcs\TestClass::protectedMethod | [protected] Removed last method parameter(s). | M100'
                ],
                'Minor change is detected.'
            ],
            'api-class-changed-method-parameter-type' => [
                $pathToFixtures . '/changed-method-parameter-type/source-code-before',
                $pathToFixtures . '/changed-method-parameter-type/source-code-after',
                [
                    'Class (MAJOR)',
                    'Test\Vcs\TestClass::testMethod | [public] Method parameter typing changed. | M113'
                ],
                'Major change is detected.'
            ],
            'api-class-new-method-parameter-type' => [
                $pathToFixtures . '/new-method-parameter-type/source-code-before',
                $pathToFixtures . '/new-method-parameter-type/source-code-after',
                [
                    'Class (MAJOR)',
                    'Test\Vcs\TestClass::testMethod | [public] Method parameter typing added. | V085'
                ],
                'Major change is detected.'
            ],
            'api-class-removed-method-parameter-type' => [
                $pathToFixtures . '/removed-method-parameter-type/source-code-before',
                $pathToFixtures . '/removed-method-parameter-type/source-code-after',
                [
                    'Class (MAJOR)',
                    'Test\Vcs\TestClass::testMethod | [public] Method parameter typing removed. | V088'
                ],
                'Major change is detected.'
            ]
        ];
    }

    private function nonApiClassChangesDataProvider()
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
        ];
    }
}
