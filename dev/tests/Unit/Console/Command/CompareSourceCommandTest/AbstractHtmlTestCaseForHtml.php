<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Console\Command\CompareSourceCommandTest;

use DOMDocument;
use DOMXPath;
use Exception;
use Magento\SemanticVersionChecker\Console\Command\CompareSourceCommand;
use Magento\SemanticVersionChecker\ReportTypes;
use PHPSemVerChecker\SemanticVersioning\Level;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Defines an abstract base class for testing
 * {@link \Magento\SemanticVersionChecker\Console\Command\CompareSourceCommand}.
 */
abstract class AbstractHtmlTestCaseForHtml extends TestCase
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
        $this->svcLogPath = TESTS_TEMP_DIR . '/svc-' . time() . '.html';
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
     * @param int $allowedChangeLevel
     * @param HtmlParseInfoContainer[] $expectedHtmlEntries
     * @param array $expectedPackageSection
     * @param string $expectedOutput
     * @param $expectedStatusCode
     * @param $reportTypes
     * @param bool $shouldSkipTest
     * @throws Exception
     */
    protected function doTestExecute(
        string $pathToSourceCodeBefore,
        string $pathToSourceCodeAfter,
        int $allowedChangeLevel,
        array $expectedHtmlEntries,
        array $expectedPackageSection,
        string $expectedOutput,
        int $expectedStatusCode,
        array $reportTypes,
        bool $shouldSkipTest
    ): void {
        try {
            $commandTester = $this->executeCommand($pathToSourceCodeBefore, $pathToSourceCodeAfter, $allowedChangeLevel, $reportTypes);
            $svcDom = $this->getSvcReportDOM();
            self::assertJsonContent($expectedPackageSection, $svcDom);
            foreach ($expectedHtmlEntries as $expectedHtmlEntry) {
                $this->assertHtml($expectedHtmlEntry->xpath, $expectedHtmlEntry->pattern, $svcDom);
            }
            $this->assertStringContainsString($expectedOutput, $commandTester->getDisplay());
            $this->assertEquals($expectedStatusCode, $commandTester->getStatusCode());
        } catch (Exception $e) {
            if ($shouldSkipTest) {
                $this->markTestSkipped($e->getMessage());
            } else {
                throw $e;
            }
        }
    }

    /**
     * Validate json in html svc document
     *
     * @param array $expectedJson
     * @param DOMDocument $docDom
     */
    private static function assertJsonContent(array $expectedJson, DOMDocument $docDom)
    {
        if (!$expectedJson) {
            $xpathQuery = '/html/body/table/tbody/tr[last()]/td[2]';
            $pattern = '#No BIC changes found to packages#i';
            self::assertHtml($xpathQuery, $pattern, $docDom);
        } else {
            $docXpath = new DOMXPath($docDom);
            $xpathQuery = '//*[@id="packageChangesJson"]/text()';
            static::assertHtml($xpathQuery, null, $docDom); //ensure xpath resolves
            $jsonText = $docDom->saveHTML($docXpath->query($xpathQuery)->item(0));
            $encodedJson = json_decode($jsonText);
            //store expectedJson in same format
            $expectedJson = json_decode(json_encode($expectedJson));
            sort($expectedJson);
            sort($encodedJson);
            self::assertEquals($expectedJson, $encodedJson);
        }
    }

    /**
     * Assert HTML document resolves xpath, resolves finding pattern, or resolves finding pattern within resolved xpath
     *
     * @param $xpathQuery
     * @param $regex
     * @param DOMDocument $docDom
     */
    public static function assertHtml($xpathQuery, $regex, DOMDocument $docDom)
    {
        $docXpath = new DOMXPath($docDom);
        if ($xpathQuery) {
            $nodeList = $docXpath->query($xpathQuery);
            if (!$nodeList || !$nodeList->length) {
                $body = $docXpath->document->saveHTML();
                static::fail('xpath selector: ' . $xpathQuery . " was invalid. Unable to return result from document:\n" . $body); //throws exception
            }
            if ($regex) {
                $body = $docDom->saveHTML($nodeList->item(0));
                static::assertMatchesRegularExpression($regex, $body);
            }
        } else {
            $body = $docXpath->document->saveHTML();
            static::assertMatchesRegularExpression($regex, $body);
        }
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
     * @param string $pathToSourceCodeBefore
     * @param string $pathToSourceCodeAfter
     * @param int $allowedChangeLevel
     * @param array $reportTypes
     * @return CommandTester
     */
    protected function executeCommand(string $pathToSourceCodeBefore, string $pathToSourceCodeAfter, int $allowedChangeLevel, array $reportTypes): CommandTester
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(
            [
                'source-before'         => $pathToSourceCodeBefore,
                'source-after'          => $pathToSourceCodeAfter,
                '--log-output-location' => $this->svcLogPath,
                '--include-patterns'    => __DIR__ . '/_files/application_includes.txt',
                '--report-type'         => $reportTypes,
                'allowed-change-level'  => $allowedChangeLevel,
            ]
        );
        return $commandTester;
    }

    /**
     * Returns the contents of the file specified in {@link CompareSourceCommandTest::$svcLogPath}.
     *
     * @return DOMDocument
     */
    private function getSvcReportDOM(): ?DOMDocument
    {
        $source = file_get_contents($this->svcLogPath);
        if (!$source) {
            return null;
        }
        $doc = new DOMDocument();
        $doc->loadHTML($source);
        return $doc;
    }
}
