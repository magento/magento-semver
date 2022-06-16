<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Console\Command;

use Magento\SemanticVersionChecker\Analyzer\NonApiAnalyzer;
use Magento\SemanticVersionChecker\ReportTypes;
use Magento\SemanticVersionChecker\Test\Unit\Console\Command\CompareSourceCommandTest\AbstractHtmlTestCaseForHtml;
use Magento\SemanticVersionChecker\Test\Unit\Console\Command\CompareSourceCommandTest\HtmlParseInfoContainer;
use PHP_CodeSniffer\Reports\Report;
use PHPSemVerChecker\SemanticVersioning\Level;
use ReflectionClass;

/**
 * Test semantic version checker CLI command dealing with API classes.
 */
class HtmlTest extends AbstractHtmlTestCaseForHtml
{
    private static $reportTypesList;


    /**
     * Test semantic version checker CLI command for classes that have the <kbd>api</kbd> annotation.
     *
     * @param string $pathToSourceCodeBefore
     * @param string $pathToSourceCodeAfter
     * @param int $allowedChangeLevel
     * @param array $expectedHtmlEntries
     * @param array $expectedPackageSection
     * @param string $expectedOutput
     * @param int $expectedStatusCode
     * @param array $reportTypes
     * @param bool $shouldSkipTest
     * @return void
     * @throws \Exception
     * @dataProvider changesDataProvider
     */
    public function testExecute(
        string $pathToSourceCodeBefore,
        string $pathToSourceCodeAfter,
        int $allowedChangeLevel,
        array $expectedHtmlEntries,
        array $expectedPackageSection,
        string $expectedOutput,
        int $expectedStatusCode,
        array $reportTypes,
        bool $shouldSkipTest = false
    ) {
        $this->doTestExecute(
            $pathToSourceCodeBefore,
            $pathToSourceCodeAfter,
            $allowedChangeLevel,
            $expectedHtmlEntries,
            $expectedPackageSection,
            $expectedOutput,
            $expectedStatusCode,
            $reportTypes,
            $shouldSkipTest
        );
    }

    public function changesDataProvider()
    {
        $pathToFixtures = __DIR__ . '/CompareSourceCommandTest/_files/all';

        return [
            'test disallowing all versioned changes for all report types (excludes NonApi)' => [
                $pathToFixtures . '/source-code-before',
                $pathToFixtures . '/source-code-after',
                Level::NONE,
                [
                    new HtmlParseInfoContainer('#MAJOR#', '//html/body/table/tbody/tr[1]/td[2]'),
                    new HtmlParseInfoContainer('#Package Level Changes#', '//html/body/table/tbody/tr[last()]/td[1]'),
                ],
                [
                    ['name' => 'test/api-class', 'level' => 'MINOR' ],
                    ['name' => 'test/api-trait', 'level' => 'MAJOR' ],
                    ['name' => 'test/layout_xml', 'level' => 'MAJOR' ],
                    ['name' => 'test/db_schema', 'level' => 'MAJOR' ],
                    ['name' => 'test/di_xml', 'level' => 'MAJOR' ],
                    ['name' => 'test/system_xml', 'level' => 'MAJOR' ],
                    ['name' => 'test/xsd-schema', 'level' => 'MAJOR' ],
                    ['name' => 'test/less', 'level' => 'MAJOR' ],
                    ['name' => 'test/mftf', 'level' => 'PATCH' ],
                ],
                'Major change is detected.',
                1,
                self::getAllReportTypes()
            ],
            'test disallowing only Major changes for all types (excludes Non-api php files)' => [
                $pathToFixtures . '/source-code-before',
                $pathToFixtures . '/source-code-after',
                Level::MINOR,
                [
                    new HtmlParseInfoContainer('#MAJOR#', '//html/body/table/tbody/tr[1]/td[2]'),
                    new HtmlParseInfoContainer('#Package Level Changes#', '//html/body/table/tbody/tr[last()]/td[1]'),
                ],
                [
                    ['name' => 'test/api-trait', 'level' => 'MAJOR' ],
                    ['name' => 'test/layout_xml', 'level' => 'MAJOR' ],
                    ['name' => 'test/db_schema', 'level' => 'MAJOR' ],
                    ['name' => 'test/di_xml', 'level' => 'MAJOR' ],
                    ['name' => 'test/system_xml', 'level' => 'MAJOR' ],
                    ['name' => 'test/xsd-schema', 'level' => 'MAJOR' ],
                    ['name' => 'test/less', 'level' => 'MAJOR' ],
                ],
                'Major change is detected.',
                1,
                self::getAllReportTypes()
            ],
            'test allowing only patch changes for all types (excludes Non-api php files)' => [
                $pathToFixtures . '/source-code-before',
                $pathToFixtures . '/source-code-after',
                Level::PATCH,
                [
                    new HtmlParseInfoContainer('#MAJOR#', '//html/body/table/tbody/tr[1]/td[2]'),
                    new HtmlParseInfoContainer('#Package Level Changes#', '//html/body/table/tbody/tr[last()]/td[1]'),
                ],
                [
                    ['name' => 'test/api-class', 'level' => 'MINOR' ],
                    ['name' => 'test/api-trait', 'level' => 'MAJOR' ],
                    ['name' => 'test/layout_xml', 'level' => 'MAJOR' ],
                    ['name' => 'test/db_schema', 'level' => 'MAJOR' ],
                    ['name' => 'test/di_xml', 'level' => 'MAJOR' ],
                    ['name' => 'test/system_xml', 'level' => 'MAJOR' ],
                    ['name' => 'test/xsd-schema', 'level' => 'MAJOR' ],
                    ['name' => 'test/less', 'level' => 'MAJOR' ],
                ],
                'Major change is detected.',
                1,
                self::getAllReportTypes()
            ],
            'test allowing all changes for all types (excludes Non-api php files)' => [
                $pathToFixtures . '/source-code-before',
                $pathToFixtures . '/source-code-after',
                Level::MAJOR,
                [
                    new HtmlParseInfoContainer('#MAJOR#', '//html/body/table/tbody/tr[1]/td[2]'),
                    new HtmlParseInfoContainer('#Package Level Changes#', '//html/body/table/tbody/tr[last()]/td[1]'),
                ],
                [],
                'Major change is detected.',
                0,
                self::getAllReportTypes()
            ],
        ];
    }

    /**
     * Get all report types
     *
     * @return array
     */
    private static function getAllReportTypes(): array
    {
        if (!self::$reportTypesList) {
            self::$reportTypesList = (new ReflectionClass(ReportTypes::class))->getConstants();
        }
        return self::$reportTypesList;
    }
}
