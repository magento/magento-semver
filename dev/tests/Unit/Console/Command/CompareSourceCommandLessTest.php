<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Console\Command;

use Magento\SemanticVersionChecker\Test\Unit\Console\Command\CompareSourceCommandTest\AbstractTestCaseWithRegExp;

/**
 * Test semantic version checker CLI command dealing with <kbd>less files</kbd>
 */
class CompareSourceCommandLessTest extends AbstractTestCaseWithRegExp
{
    /**
     * Test semantic version checker CLI command for changes in <kbd>*.less</kbd> files.
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
        $pathToFixtures = __DIR__ . '/CompareSourceCommandTest/_files/less';

        return [
            'no-change' => [
                $pathToFixtures . '/no-change/source-code-before',
                $pathToFixtures . '/no-change/source-code-after',
                [
                    '/Suggested semantic versioning change: NONE/',
                ],
                'None change is detected.',
            ],
            'removed-variable' => [
                $pathToFixtures . '/removed-variable/source-code-before',
                $pathToFixtures . '/removed-variable/source-code-after',
                [
                    '/Less \(MAJOR\)/',
                    '/view\/frontend\/web\/css\/source\/test.less:0\s*\|\s*@line-height__large\s*\|\s*A less variable-node was removed\s*\|\s*M400/'
                ],
                'Major change is detected.',
            ],
            'removed-variables' => [
                $pathToFixtures . '/removed-variables/source-code-before',
                $pathToFixtures . '/removed-variables/source-code-after',
                [
                    '/Less \(MAJOR\)/',
                    '/view\/frontend\/web\/css\/source\/test.less:0\s*\|\s*@line-height__large\s*\|\s*A less variable-node was removed\s*\|\s*M400/',
                    '/view\/frontend\/web\/css\/source\/test.less:0\s*\|\s*@line-height__standard\s*\|\s*A less variable-node was removed\s*\|\s*M400/'
                ],
                'Major change is detected.',
            ],
            'removed-mixin' => [
                $pathToFixtures . '/removed-mixin/source-code-before',
                $pathToFixtures . '/removed-mixin/source-code-after',
                [
                    '/Less \(MAJOR\)/',
                    '/view\/frontend\/web\/css\/source\/test.less:0\s*\|\s*.round-borders-large\s*\|\s*A less mixin-node was removed\s*\|\s*M401/'
                ],
                'Major change is detected.',
            ],
            'removed-mixins' => [
                $pathToFixtures . '/removed-mixins/source-code-before',
                $pathToFixtures . '/removed-mixins/source-code-after',
                [
                    '/Less \(MAJOR\)/',
                    '/view\/frontend\/web\/css\/source\/test.less:0\s*\|\s*.round-borders-large\s*\|\s*A less mixin-node was removed\s*\|\s*M401/',
                    '/view\/frontend\/web\/css\/source\/test.less:0\s*\|\s*.round-borders-small\s*\|\s*A less mixin-node was removed\s*\|\s*M401/'
                ],
                'Major change is detected.',
            ],
            'removed-import' => [
                $pathToFixtures . '/removed-import/source-code-before',
                $pathToFixtures . '/removed-import/source-code-after',
                [
                    '/Less \(MAJOR\)/',
                    '/view\/frontend\/web\/css\/source\/test.less:0\s*\|\s*Import with value: \'testimport\'\s*\|\s*A less import-node was removed\s*\|\s*M402/'
                ],
                'Major change is detected.',
            ],
            'removed-imports' => [
                $pathToFixtures . '/removed-imports/source-code-before',
                $pathToFixtures . '/removed-imports/source-code-after',
                [
                    '/Less \(MAJOR\)/',
                    '/view\/frontend\/web\/css\/source\/test.less:0\s*\|\s*Import with value: \'testimport\'\s*\|\s*A less import-node was removed\s*\|\s*M402/',
                    '/view\/frontend\/web\/css\/source\/test.less:0\s*\|\s*Import with value: \'testimport2\'\s*\|\s*A less import-node was removed\s*\|\s*M402/'
                ],
                'Major change is detected.',
            ],
            'added-mixin-parameter' => [
                $pathToFixtures . '/added-mixin-parameter/source-code-before',
                $pathToFixtures . '/added-mixin-parameter/source-code-after',
                [
                    '/Less \(MAJOR\)/',
                    '/view\/frontend\/web\/css\/source\/test.less:0\s*\|\s*.round-borders:@radius\s*\|\s*A parameter was added to the mixin-node\s*\|\s*M403/'
                ],
                'Major change is detected.',
            ],
            'added-first-mixin-parameter' => [
                $pathToFixtures . '/added-first-mixin-parameter/source-code-before',
                $pathToFixtures . '/added-first-mixin-parameter/source-code-after',
                [
                    '/Less \(MAJOR\)/',
                    '/view\/frontend\/web\/css\/source\/test.less:0\s*\|\s*.round-borders:@radius1\s*\|\s*A parameter was added to the mixin-node\s*\|\s*M403/'
                ],
                'Major change is detected.',
            ],
            'added-middle-mixin-parameters' => [
                $pathToFixtures . '/added-middle-mixin-parameters/source-code-before',
                $pathToFixtures . '/added-middle-mixin-parameters/source-code-after',
                [
                    '/Less \(MAJOR\)/',
                    '/view\/frontend\/web\/css\/source\/test.less:0\s*\|\s*.round-borders:@radius2, @radius3\s*\|\s*A parameter was added to the mixin-node\s*\|\s*M403/'
                ],
                'Major change is detected.',
            ],
            'added-last-mixin-parameter' => [
                $pathToFixtures . '/added-last-mixin-parameter/source-code-before',
                $pathToFixtures . '/added-last-mixin-parameter/source-code-after',
                [
                    '/Less \(MAJOR\)/',
                    '/view\/frontend\/web\/css\/source\/test.less:0\s*\|\s*.round-borders:@radius3\s*\|\s*A parameter was added to the mixin-node\s*\|\s*M403/'
                ],
                'Major change is detected.',
            ]
        ];
    }
}
