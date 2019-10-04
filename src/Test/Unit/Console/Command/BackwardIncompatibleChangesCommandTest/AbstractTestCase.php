<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\SemanticVersionChecker\Test\Unit\Console\Command\BackwardIncompatibleChangesCommandTest;

use Magento\Tools\SemanticVersionChecker\Console\Command\BackwardIncompatibleChangesCommand;
use Magento\Tools\SemanticVersionChecker\Reporter\BreakingChangeTableReporter;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Defines an abstract base class for testing
 * {@link \Magento\Tools\SemanticVersionChecker\Console\Command\BackwardIncompatibleChangesCommand}.
 */
abstract class AbstractTestCase extends \PHPUnit\Framework\TestCase
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
     * Assert that the number of table rows is as expected and all changes are present in the table
     *
     * @param array $expectedChanges
     * @param string $actualReport
     * @param string $reportType
     * @return void
     */
    protected function assertExpectedChanges($expectedChanges, $actualReport, $reportType)
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

    /**
     * Executes the command that shall be tested and performs assertions.
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
     * @throws \Exception
     */
    protected function doTestExecute(
        $pathToSourceCodeBefore,
        $pathToSourceCodeAfter,
        $expectedBreakingChanges,
        $expectedMembershipChanges,
        $shouldSkipTest
    ): void {
        try {
            $commandTester = $this->executeCommand($pathToSourceCodeBefore, $pathToSourceCodeAfter);
            $actualReport  = $this->getActualReport();

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

    /**
     * Executes {@link BackwardIncompatibleChangesCommandTest::$command} via {@link CommandTester}, using the arguments
     * as command line parameters.
     *
     * The command line parameters are specified as follows:
     * <ul>
     *   <li><kbd>source-before</kbd>: The content of the argument <var>$pathToSourceCodeBefore</var></li>
     *   <li><kbd>source-after</kbd>: The content of the argument <var>$pathToSourceCodeAfter</var></li>
     *   <li><kbd>target-file</kbd>: The content of {@link BackwardIncompatibleChangesCommandTest::$bicFilePath}</li>
     *   <li><kbd>--include-patterns</kbd>: The path to the file <kbd>./_files/application_includes.txt</kbd></li>
     * </ul>
     *
     * @param string $pathToSourceCodeBefore
     * @param string $pathToSourceCodeAfter
     * @return CommandTester The object that was used to actually execute the command
     */
    private function executeCommand($pathToSourceCodeBefore, $pathToSourceCodeAfter): CommandTester
    {
        $commandTester = new CommandTester($this->command);

        $commandTester->execute(
            [
                'source-before'      => $pathToSourceCodeBefore,
                'source-after'       => $pathToSourceCodeAfter,
                'target-file'        => $this->bicFilePath,
                '--include-patterns' => __DIR__ . '/_files/application_includes.txt'
            ]
        );
        return $commandTester;
    }

    /**
     * Returns the contents of the file specified in {@link BackwardIncompatibleChangesCommandTest::$bicFilePath}.
     *
     * @return false|string
     */
    private function getActualReport()
    {
        return file_get_contents($this->bicFilePath);
    }
}
