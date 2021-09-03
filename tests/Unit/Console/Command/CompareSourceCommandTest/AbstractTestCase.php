<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Console\Command\CompareSourceCommandTest;

use Magento\SemanticVersionChecker\Console\Command\CompareSourceCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Defines an abstract base class for testing
 * {@link \Magento\SemanticVersionChecker\Console\Command\CompareSourceCommand}.
 */
abstract class AbstractTestCase extends TestCase
{
    /**
     * @var CompareSourceCommand
     */
    protected $command;

    /**
     * @var string
     */
    protected $svcLogPath;

    protected function setUp(): void
    {
        $this->command = new CompareSourceCommand();
        $this->svcLogPath = TESTS_TEMP_DIR . '/svc-' . time() . '.log';
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unlink($this->svcLogPath);
    }

    /**
     * Executes the command that shall be tested and performs assertions.
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
     * @param string[] $unexpectedLogEntries
     * @throws \Exception
     */
    protected function doTestExecute(
        $pathToSourceCodeBefore,
        $pathToSourceCodeAfter,
        $expectedLogEntries,
        $expectedOutput,
        $unexpectedLogEntries
    ): void {
        $commandTester        = $this->executeCommand($pathToSourceCodeBefore, $pathToSourceCodeAfter);
        $actualSvcLogContents = $this->getActualSvcLogContents();
        $preparedSvcLogContents = preg_replace('/\s+/', '', $actualSvcLogContents);

        foreach ($expectedLogEntries as $expectedLogEntry) {
            $this->assertStringContainsString(
                preg_replace('/\s+/', '', $expectedLogEntry),
                $preparedSvcLogContents,
                'Failed asserting that "' . $actualSvcLogContents . '" contains "' . $expectedLogEntry . '"'
            );
        }
        foreach ($unexpectedLogEntries as $unexpectedLogEntry) {
            $this->assertStringNotContainsString(
                preg_replace('/\s+/', '', $unexpectedLogEntry),
                $preparedSvcLogContents,
                'Failed asserting that "' . $actualSvcLogContents . '" doesn\'t contain "' . $unexpectedLogEntry . '"'
            );
        }
        $this->assertStringContainsString($expectedOutput, $commandTester->getDisplay());
        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    /**
     * Executes {@link CompareSourceCommandTest::$command} via {@link CommandTester}, using the arguments as command
     * line parameters.
     *
     * The command line parameters are specified as follows:
     * <ul>
     *   <li><kbd>source-before</kbd>: The content of the argument <var>$pathToSourceCodeBefore</var></li>
     *   <li><kbd>source-after</kbd>: The content of the argument <var>$pathToSourceCodeAfter</var></li>
     *   <li><kbd>--log-output-location</kbd>: The content of {@link CompareSourceCommandTest::$svcLogPath}</li>
     *   <li><kbd>--include-patterns</kbd>: The path to the file <kbd>./_files/application_includes.txt</kbd></li>
     * </ul>
     *
     * @param $pathToSourceCodeBefore
     * @param $pathToSourceCodeAfter
     * @return CommandTester
     */
    protected function executeCommand($pathToSourceCodeBefore, $pathToSourceCodeAfter): CommandTester
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(
            [
                'source-before'         => $pathToSourceCodeBefore,
                'source-after'          => $pathToSourceCodeAfter,
                '--log-output-location' => $this->svcLogPath,
                '--include-patterns'    => __DIR__ . '/_files/application_includes.txt',
            ]
        );
        return $commandTester;
    }

    /**
     * Returns the contents of the file specified in {@link CompareSourceCommandTest::$svcLogPath}.
     *
     * @return false|string
     */
    private function getActualSvcLogContents()
    {
        return file_get_contents($this->svcLogPath);
    }
}
