<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Console\Command;

use Magento\SemanticVersionChecker\Test\Unit\Console\Command\CompareSourceCommandTest\AbstractHtmlTestCaseForHtml;
use Magento\SemanticVersionChecker\Test\Unit\Console\Command\CompareSourceCommandTest\HtmlParseInfoContainer;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * Test semantic version checker CLI command dealing with API classes.
 */
class HtmlTest extends AbstractHtmlTestCaseForHtml
{
    /**
     * Test semantic version checker CLI command for classes that have the <kbd>api</kbd> annotation.
     *
     * @param string $pathToSourceCodeBefore
     * @param string $pathToSourceCodeAfter
     * @param int $allowedChangeLevel
     * @param $expectedHtmlEntries
     * @param $expectedPackageSection
     * @param string $expectedOutput
     * @param $expectedStatusCode
     * @param bool $shouldSkipTest
     * @return void
     * @throws \Exception
     * @dataProvider changesDataProvider
     */
    public function testExecute(
        $pathToSourceCodeBefore,
        $pathToSourceCodeAfter,
        $allowedChangeLevel,
        $expectedHtmlEntries,
        $expectedPackageSection,
        $expectedOutput,
        $expectedStatusCode,
        $shouldSkipTest = false
    ) {
        $this->doTestExecute(
            $pathToSourceCodeBefore,
            $pathToSourceCodeAfter,
            $allowedChangeLevel,
            $expectedHtmlEntries,
            $expectedPackageSection,
            $expectedOutput,
            $expectedStatusCode,
            $shouldSkipTest
        );
    }

    public function changesDataProvider()
    {
        $pathToFixtures = __DIR__ . '/CompareSourceCommandTest/_files/all';

        return [
            'test all levels of changes of all types' => [
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
                    ['name' => 'test/di_xml', 'level' => 'MAJOR' ],
                    ['name' => 'test/system_xml', 'level' => 'MAJOR' ],
                    ['name' => 'test/xsd-schema', 'level' => 'MAJOR' ],
                    ['name' => 'test/less-schema', 'level' => 'MAJOR' ],
                ],
                'Major change is detected.',
                -1,
            ],
            'test only Major changes for all types' => [
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
                    ['name' => 'test/di_xml', 'level' => 'MAJOR' ],
                    ['name' => 'test/system_xml', 'level' => 'MAJOR' ],
                    ['name' => 'test/xsd-schema', 'level' => 'MAJOR' ],
                    ['name' => 'test/less-schema', 'level' => 'MAJOR' ],
                ],
                'Major change is detected.',
                -1,
            ],
            'test allowing all changes for all types' => [
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
            ],


        ];
    }
}
