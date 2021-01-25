<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tools\SemanticVersionChecker\Test\Unit\Console\Command;

use Magento\SemanticVersionChecker\Test\Unit\Console\Command\CompareSourceCommandTest\AbstractTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Test semantic version checker CLI command.
 */
class CompareSourceCommandMftfTest extends AbstractTestCase
{
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
     * @param string[] $unexpectedLogEntries
     * @dataProvider changesDataProvider
     * @return void
     * @throws \Exception
     */
    public function testExecute(
        $pathToSourceCodeBefore,
        $pathToSourceCodeAfter,
        $expectedLogEntries,
        $expectedOutput,
        $unexpectedLogEntries = []
    ) {
        $this->doTestExecute(
            $pathToSourceCodeBefore,
            $pathToSourceCodeAfter,
            $expectedLogEntries,
            $expectedOutput,
            $unexpectedLogEntries
        );
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
     *   <li><kbd>--mftf</kbd>: The content of the argument <var>None</var></li>
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
                '--include-patterns'    => __DIR__ . '/CompareSourceCommandTest/_files/application_includes.txt',
                '--report-type'         => ['mftf'],
            ]
        );
        return $commandTester;
    }

    public function changesDataProvider()
    {
        $pathToFixtures = __DIR__ . '/CompareSourceCommandTest/_files/mftf';
        return [
            'actionGroup-removed' => [
                $pathToFixtures . '/actionGroup-removed/source-code-before',
                $pathToFixtures . '/actionGroup-removed/source-code-after',
                [
                    'Mftf (MAJOR)',
                    'actionGroup-removed/source-code-before/Magento/TestModule/Test/Mftf/ActionGroup/actionGroup.xml:0',
                    'ActionGroup/ActionGroup1 | <actionGroup> was removed | M200'
                ],
                'Major change is detected.'
            ],
            'actionGroup-added' => [
                $pathToFixtures . '/actionGroup-added/source-code-before',
                $pathToFixtures . '/actionGroup-added/source-code-after',
                [
                    'Mftf (MINOR)',
                    'actionGroup-added/source-code-after/Magento/TestModule/Test/Mftf/ActionGroup/actionGroup.xml:0',
                    'ActionGroup/ActionGroup2 | <actionGroup> was added | M225'
                ],
                'Minor change is detected.'
            ],
            'new-module-actionGroup-added' => [
                $pathToFixtures . '/new-module-actionGroup-added/source-code-before',
                $pathToFixtures . '/new-module-actionGroup-added/source-code-after',
                [
                    'Mftf (MINOR)',
                    'new-module-actionGroup-added/source-code-after/Magento/TestModuleTwo/Test/Mftf/ActionGroup/actionGroup.xml:0',
                    'ActionGroup/ActionGroup2 | <actionGroup> was added | M225'
                ],
                'Minor change is detected.'
            ],
            'actionGroup-argument-changed' => [
                $pathToFixtures . '/actionGroup-argument-changed/source-code-before',
                $pathToFixtures . '/actionGroup-argument-changed/source-code-after',
                [
                    'Mftf (MAJOR)',
                    'actionGroup-argument-changed/source-code-before/Magento/TestModule/Test/Mftf/ActionGroup/actionGroup.xml:0',
                    'ActionGroup/ActionGroup1/arg1/type | <actionGroup> <argument> was changed | M203'
                ],
                'Major change is detected.'
            ],
            'actionGroup-argument-removed' => [
                $pathToFixtures . '/actionGroup-argument-removed/source-code-before',
                $pathToFixtures . '/actionGroup-argument-removed/source-code-after',
                [
                    'Mftf (MAJOR)',
                    'actionGroup-argument-removed/source-code-before/Magento/TestModule/Test/Mftf/ActionGroup/actionGroup.xml:0',
                    'ActionGroup/ActionGroup1/Arguments/arg1 | <actionGroup> <argument> was removed | M201'
                ],
                'Major change is detected.'
            ],
            'actionGroup-argument-added' => [
                $pathToFixtures . '/actionGroup-argument-added/source-code-before',
                $pathToFixtures . '/actionGroup-argument-added/source-code-after',
                [
                    'Mftf (MAJOR)',
                    'actionGroup-argument-added/source-code-before/Magento/TestModule/Test/Mftf/ActionGroup/actionGroup.xml:0',
                    'ActionGroup/ActionGroup1/arg2 | <actionGroup> <argument> was added | M227'
                ],
                'Major change is detected.'
            ],
            'actionGroup-action-changed' => [
                $pathToFixtures . '/actionGroup-action-changed/source-code-before',
                $pathToFixtures . '/actionGroup-action-changed/source-code-after',
                [
                    'Mftf (PATCH)',
                    'actionGroup-action-changed/source-code-before/Magento/TestModule/Test/Mftf/ActionGroup/actionGroup.xml:0',
                    'ActionGroup/ActionGroup1/action1/userInput | <actionGroup> <action> was changed | M204'
                ],
                'Patch change is detected.'
            ],
            'actionGroup-action-type-changed' => [
                $pathToFixtures . '/actionGroup-action-type-changed/source-code-before',
                $pathToFixtures . '/actionGroup-action-type-changed/source-code-after',
                [
                    'Mftf (PATCH)',
                    'actionGroup-action-type-changed/source-code-before/Magento/TestModule/Test/Mftf/ActionGroup/actionGroup.xml:0',
                    'ActionGroup/ActionGroup1/action1 | <actionGroup> <action> type was changed | M223'
                ],
                'Patch change is detected.'
            ],
            'actionGroup-action-removed' => [
                $pathToFixtures . '/actionGroup-action-removed/source-code-before',
                $pathToFixtures . '/actionGroup-action-removed/source-code-after',
                [
                    'Mftf (MAJOR)',
                    'actionGroup-action-removed/source-code-before/Magento/TestModule/Test/Mftf/ActionGroup/actionGroup.xml:0',
                    'ActionGroup/ActionGroup1/action2 | <actionGroup> <action> was removed | M202'
                ],
                'Major change is detected.'
            ],
            'actionGroup-action-added' => [
                $pathToFixtures . '/actionGroup-action-added/source-code-before',
                $pathToFixtures . '/actionGroup-action-added/source-code-after',
                [
                    'Mftf (MINOR)',
                    'actionGroup-action-added/source-code-before/Magento/TestModule/Test/Mftf/ActionGroup/actionGroup.xml:0',
                    'ActionGroup/ActionGroup1/action3 | <actionGroup> <action> was added | M226'
                ],
                'Minor change is detected.'
            ],
            'data-removed' => [
                $pathToFixtures . '/data-removed/source-code-before',
                $pathToFixtures . '/data-removed/source-code-after',
                [
                    'Mftf (MAJOR)',
                    'data-removed/source-code-before/Magento/TestModule/Test/Mftf/Data/data.xml:0',
                    'Data/DataEntity1 | Entity was removed | M205'
                ],
                'Major change is detected.'
            ],
            'data-added' => [
                $pathToFixtures . '/data-added/source-code-before',
                $pathToFixtures . '/data-added/source-code-after',
                [
                    'Mftf (MINOR)',
                    'data-added/source-code-after/Magento/TestModule/Test/Mftf/Data/data.xml:0',
                    'Data/DataEntity2 | <entity> was added | M228'
                ],
                'Minor change is detected.'
            ],
            'new-module-data-added' => [
                $pathToFixtures . '/new-module-data-added/source-code-before',
                $pathToFixtures . '/new-module-data-added/source-code-after',
                [
                    'Mftf (MINOR)',
                    'new-module-data-added/source-code-after/Magento/TestModuleTwo/Test/Mftf/Data/data.xml:0',
                    'Data/DataEntity2 | <entity> was added | M228'
                ],
                'Minor change is detected.'
            ],
            'data-array-removed' => [
                $pathToFixtures . '/data-array-removed/source-code-before',
                $pathToFixtures . '/data-array-removed/source-code-after',
                [
                    'Mftf (MAJOR)',
                    'data-array-removed/source-code-before/Magento/TestModule/Test/Mftf/Data/data.xml:0',
                    'Data/DataEntity1/arraykey | Entity <array> element was removed | M206'
                ],
                'Major change is detected.'
            ],
            'data-array-added' => [
                $pathToFixtures . '/data-array-added/source-code-before',
                $pathToFixtures . '/data-array-added/source-code-after',
                [
                    'Mftf (MINOR)',
                    'data-array-added/source-code-before/Magento/TestModule/Test/Mftf/Data/data.xml:0',
                    'Data/DataEntity1/arraykeynew | <entity> <array> was added | M229'
                ],
                'Minor change is detected.'
            ],
            'data-array-item-removed' => [
                $pathToFixtures . '/data-array-item-removed/source-code-before',
                $pathToFixtures . '/data-array-item-removed/source-code-after',
                [
                    'Mftf (MINOR)',
                    'data-array-item-removed/source-code-before/Magento/TestModule/Test/Mftf/Data/data.xml:0',
                    'Data/DataEntity1/arraykey/(tre) | Entity <array> <item> element was removed | M207'
                ],
                'Minor change is detected.'
            ],
            'data-field-removed' => [
                $pathToFixtures . '/data-field-removed/source-code-before',
                $pathToFixtures . '/data-field-removed/source-code-after',
                [
                    'Mftf (MAJOR)',
                    'data-field-removed/source-code-before/Magento/TestModule/Test/Mftf/Data/data.xml:0',
                    'Data/DataEntity1/datakey | Entity <data> element was removed | M208'
                ],
                'Major change is detected.'
            ],
            'data-field-added' => [
                $pathToFixtures . '/data-field-added/source-code-before',
                $pathToFixtures . '/data-field-added/source-code-after',
                [
                    'Mftf (MINOR)',
                    'data-field-added/source-code-before/Magento/TestModule/Test/Mftf/Data/data.xml:0',
                    'Data/DataEntity1/datakeynew | Entity <data> element was added | M230'
                ],
                'Minor change is detected.'
            ],
            'data-reqentity-removed' => [
                $pathToFixtures . '/data-reqentity-removed/source-code-before',
                $pathToFixtures . '/data-reqentity-removed/source-code-after',
                [
                    'Mftf (MAJOR)',
                    'data-reqentity-removed/source-code-before/Magento/TestModule/Test/Mftf/Data/data.xml:0',
                    'Data/DataEntity1/reqentity | Entity <required-entity> element was removed | M209'
                ],
                'Major change is detected.'
            ],
            'data-reqentity-added' => [
                $pathToFixtures . '/data-reqentity-added/source-code-before',
                $pathToFixtures . '/data-reqentity-added/source-code-after',
                [
                    'data-reqentity-added/source-code-before/Magento/TestModule/Test/Mftf/Data/data.xml:0',
                    'Mftf (PATCH)',
                    'Data/DataEntity1/reqnew | <entity> <required-entity> element was added | M231'
                ],
                'Patch change is detected.'
            ],
            'data-var-removed' => [
                $pathToFixtures . '/data-var-removed/source-code-before',
                $pathToFixtures . '/data-var-removed/source-code-after',
                [
                    'data-var-removed/source-code-before/Magento/TestModule/Test/Mftf/Data/data.xml:0',
                    'Mftf (MAJOR)',
                    'Data/DataEntity1/var1 | Entity <var> element was removed | M210'
                ],
                'Major change is detected.'
            ],
            'data-var-added' => [
                $pathToFixtures . '/data-var-added/source-code-before',
                $pathToFixtures . '/data-var-added/source-code-after',
                [
                    'Mftf (MINOR)',
                    'data-var-added/source-code-before/Magento/TestModule/Test/Mftf/Data/data.xml:0',
                    'Data/DataEntity1/var2 | <entity> <var> element was added | M232'
                ],
                'Minor change is detected.'
            ],
            'metadata-removed' => [
                $pathToFixtures . '/metadata-removed/source-code-before',
                $pathToFixtures . '/metadata-removed/source-code-after',
                [
                    'metadata-removed/source-code-before/Magento/TestModule/Test/Mftf/Metadata/meta.xml:0',
                    'Mftf (MAJOR)',
                    'Metadata/createEntity | <operation> was removed | M211'
                ],
                'Major change is detected.'
            ],
            'metadata-added' => [
                $pathToFixtures . '/metadata-added/source-code-before',
                $pathToFixtures . '/metadata-added/source-code-after',
                [
                    'Mftf (MINOR)',
                    'metadata-added/source-code-after/Magento/TestModule/Test/Mftf/Metadata/meta.xml:0',
                    'Metadata/createEntity2 | <operation> was added | M240'
                ],
                'Minor change is detected.'
            ],
            'new-module-metadata-added' => [
                $pathToFixtures . '/new-module-metadata-added/source-code-before',
                $pathToFixtures . '/new-module-metadata-added/source-code-after',
                [
                    'Mftf (MINOR)',
                    'new-module-metadata-added/source-code-after/Magento/TestModuleTwo/Test/Mftf/Metadata/meta.xml:0',
                    'Metadata/createEntity2 | <operation> was added | M240'
                ],
                'Minor change is detected.'
            ],
            'metadata-datatype-changed' => [
                $pathToFixtures . '/metadata-datatype-changed/source-code-before',
                $pathToFixtures . '/metadata-datatype-changed/source-code-after',
                [
                    'Mftf (MINOR)',
                    'metadata-datatype-changed/source-code-before/Magento/TestModule/Test/Mftf/Metadata/meta.xml:0',
                    'Metadata/createEntity/dataType | <operation> was changed | M241'
                ],
                'Minor change is detected.'
            ],
            'metadata-type-changed' => [
                $pathToFixtures . '/metadata-type-changed/source-code-before',
                $pathToFixtures . '/metadata-type-changed/source-code-after',
                [
                    'metadata-type-changed/source-code-before/Magento/TestModule/Test/Mftf/Metadata/meta.xml:0',
                    'Mftf (MINOR)',
                    'Metadata/createEntity/type | <operation> was changed | M241'
                ],
                'Minor change is detected.'
            ],
            'metadata-auth-changed' => [
                $pathToFixtures . '/metadata-auth-changed/source-code-before',
                $pathToFixtures . '/metadata-auth-changed/source-code-after',
                [
                    'metadata-auth-changed/source-code-before/Magento/TestModule/Test/Mftf/Metadata/meta.xml:0',
                    'Mftf (MINOR)',
                    'Metadata/createEntity/auth | <operation> was changed | M241'
                ],
                'Minor change is detected.'
            ],
            'metadata-url-changed' => [
                $pathToFixtures . '/metadata-url-changed/source-code-before',
                $pathToFixtures . '/metadata-url-changed/source-code-after',
                [
                    'metadata-url-changed/source-code-before/Magento/TestModule/Test/Mftf/Metadata/meta.xml:0',
                    'Mftf (MINOR)',
                    'Metadata/createEntity/url | <operation> was changed | M241'
                ],
                'Minor change is detected.'
            ],
            'metadata-method-changed' => [
                $pathToFixtures . '/metadata-method-changed/source-code-before',
                $pathToFixtures . '/metadata-method-changed/source-code-after',
                [
                    'Mftf (MINOR)',
                    'metadata-method-changed/source-code-before/Magento/TestModule/Test/Mftf/Metadata/meta.xml:0',
                    'Metadata/createEntity/method | <operation> was changed | M241'
                ],
                'Minor change is detected.'
            ],
            'metadata-top-level-child-removed' => [
                $pathToFixtures . '/metadata-top-level-child-removed/source-code-before',
                $pathToFixtures . '/metadata-top-level-child-removed/source-code-after',
                [
                    'metadata-top-level-child-removed/source-code-before/Magento/TestModule/Test/Mftf/Metadata/meta.xml:0',
                    'Mftf (MAJOR)',
                    'Metadata/createEntity/toplevelField | <operation> child element was removed | M212'
                ],
                'Major change is detected.'
            ],
            'metadata-top-level-child-added' => [
                $pathToFixtures . '/metadata-top-level-child-added/source-code-before',
                $pathToFixtures . '/metadata-top-level-child-added/source-code-after',
                [
                    'Mftf (MINOR)',
                    'metadata-top-level-child-added/source-code-before/Magento/TestModule/Test/Mftf/Metadata/meta.xml:0',
                    'Metadata/createEntity/toplevelField | <operation> child element was added | M242'
                ],
                'Minor change is detected.'
            ],
            'metadata-bottom-level-child-removed' => [
                $pathToFixtures . '/metadata-bottom-level-child-removed/source-code-before',
                $pathToFixtures . '/metadata-bottom-level-child-removed/source-code-after',
                [
                    'Mftf (MAJOR)',
                    'metadata-bottom-level-child-removed/source-code-before/Magento/TestModule/Test/Mftf/Metadata/meta.xml:0',
                    'Metadata/createEntity/toplevelObj/childField | <operation> child element was removed | M212'
                ],
                'Major change is detected.'
            ],
            'metadata-bottom-level-child-added' => [
                $pathToFixtures . '/metadata-bottom-level-child-added/source-code-before',
                $pathToFixtures . '/metadata-bottom-level-child-added/source-code-after',
                [
                    'Mftf (MINOR)',
                    'metadata-bottom-level-child-added/source-code-before/Magento/TestModule/Test/Mftf/Metadata/meta.xml:0',
                    'Metadata/createEntity/toplevelObj/childField | <operation> child element was added | M242'
                ],
                'Minor change is detected.'
            ],
            'page-removed' => [
                $pathToFixtures . '/page-removed/source-code-before',
                $pathToFixtures . '/page-removed/source-code-after',
                [
                    'Mftf (MAJOR)',
                    'page-removed/source-code-before/Magento/TestModule/Test/Mftf/Page/page.xml:0',
                    'Page/SamplePage | <page> was removed | M213'
                ],
                'Major change is detected.'
            ],
            'page-added' => [
                $pathToFixtures . '/page-added/source-code-before',
                $pathToFixtures . '/page-added/source-code-after',
                [
                    'page-added/source-code-after/Magento/TestModule/Test/Mftf/Page/page.xml:0',
                    'Mftf (MINOR)',
                    'Page/SamplePageNew | <page> was added | M233'
                ],
                'Minor change is detected.'
            ],
            'new-module-page-added' => [
                $pathToFixtures . '/new-module-page-added/source-code-before',
                $pathToFixtures . '/new-module-page-added/source-code-after',
                [
                    'new-module-page-added/source-code-after/Magento/TestModuleTwo/Test/Mftf/Page/page.xml:0',
                    'Mftf (MINOR)',
                    'Page/SamplePageNew | <page> was added | M233'
                ],
                'Minor change is detected.'
            ],
            'page-section-removed' => [
                $pathToFixtures . '/page-section-removed/source-code-before',
                $pathToFixtures . '/page-section-removed/source-code-after',
                [
                    'Mftf (MAJOR)',
                    'page-section-removed/source-code-before/Magento/TestModule/Test/Mftf/Page/page.xml:0',
                    'Page/SamplePage/Section2 | <page> <section> was removed | M214'
                ],
                'Major change is detected.'
            ],
            'page-section-added' => [
                $pathToFixtures . '/page-section-added/source-code-before',
                $pathToFixtures . '/page-section-added/source-code-after',
                [
                    'Mftf (MINOR)',
                    'page-section-added/source-code-before/Magento/TestModule/Test/Mftf/Page/page.xml:0',
                    'Page/SamplePage/SectionNew | <page> <section> was added | M234'
                ],
                'Minor change is detected.'
            ],
            'section-removed' => [
                $pathToFixtures . '/section-removed/source-code-before',
                $pathToFixtures . '/section-removed/source-code-after',
                [
                    'section-removed/source-code-before/Magento/TestModule/Test/Mftf/Section/section.xml:0',
                    'Mftf (MAJOR)',
                    'Section/SampleSection | <section> was removed | M215'
                ],
                'Major change is detected.'
            ],
            'section-added' => [
                $pathToFixtures . '/section-added/source-code-before',
                $pathToFixtures . '/section-added/source-code-after',
                [
                    'Mftf (MINOR)',
                    'section-added/source-code-after/Magento/TestModule/Test/Mftf/Section/section.xml:0',
                    'Section/NewSection | <section> was added | M235'
                ],
                'Minor change is detected.'
            ],
            'new-module-section-added' => [
                $pathToFixtures . '/new-module-section-added/source-code-before',
                $pathToFixtures . '/new-module-section-added/source-code-after',
                [
                    'Mftf (MINOR)',
                    'new-module-section-added/source-code-after/Magento/TestModuleTwo/Test/Mftf/Section/section.xml:0',
                    'Section/NewSection | <section> was added | M235'
                ],
                'Minor change is detected.'
            ],
            'section-element-removed' => [
                $pathToFixtures . '/section-element-removed/source-code-before',
                $pathToFixtures . '/section-element-removed/source-code-after',
                [
                    'Mftf (MAJOR)',
                    'section-element-removed/source-code-before/Magento/TestModule/Test/Mftf/Section/section.xml:0',
                    'Section/SampleSection/element2 | <section> <element> was removed | M216'
                ],
                'Major change is detected.'
            ],
            'section-element-selector-changed' => [
                $pathToFixtures . '/section-element-selector-changed/source-code-before',
                $pathToFixtures . '/section-element-selector-changed/source-code-after',
                [
                    'Mftf (PATCH)',
                    'section-element-selector-changed/source-code-before/Magento/TestModule/Test/Mftf/Section/section.xml:0',
                    'Section/SampleSection/element1/selector | <section> <element> selector was changed | M219'
                ],
                'Patch change is detected.'
            ],
            'section-element-type-changed' => [
                $pathToFixtures . '/section-element-type-changed/source-code-before',
                $pathToFixtures . '/section-element-type-changed/source-code-after',
                [
                    'Mftf (PATCH)',
                    'section-element-type-changed/source-code-before/Magento/TestModule/Test/Mftf/Section/section.xml:0',
                    'Section/SampleSection/element1/type | <section> <element> type was changed | M218'
                ],
                'Patch change is detected.'
            ],
            'section-element-parameterized-added' => [
                $pathToFixtures . '/section-element-parameterized-added/source-code-before',
                $pathToFixtures . '/section-element-parameterized-added/source-code-after',
                [
                    'section-element-parameterized-added/source-code-before/Magento/TestModule/Test/Mftf/Section/section.xml:0',
                    'Mftf (MAJOR)',
                    'Section/SampleSection/element1/parameterized | <section> <element> parameterized was changed | M250'
                ],
                'Major change is detected.'
            ],
            'section-element-parameterized-removed' => [
                $pathToFixtures . '/section-element-parameterized-removed/source-code-before',
                $pathToFixtures . '/section-element-parameterized-removed/source-code-after',
                [
                    'Mftf (MAJOR)',
                    'section-element-parameterized-removed/source-code-before/Magento/TestModule/Test/Mftf/Section/section.xml:0',
                    'Section/SampleSection/element1/parameterized | <section> <element> parameterized was changed | M250'
                ],
                'Major change is detected.'
            ],
            'section-element-added' => [
                $pathToFixtures . '/section-element-added/source-code-before',
                $pathToFixtures . '/section-element-added/source-code-after',
                [
                    'Mftf (MINOR)',
                    'section-element-added/source-code-before/Magento/TestModule/Test/Mftf/Section/section.xml:0',
                    'Section/SampleSection/newElement | <section> <element> was added | M236'
                ],
                'Minor change is detected.'
            ],
            'test-removed' => [
                $pathToFixtures . '/test-removed/source-code-before',
                $pathToFixtures . '/test-removed/source-code-after',
                [
                    'Mftf (MAJOR)',
                    'test-removed/source-code-before/Magento/TestModule/Test/Mftf/Test/test.xml:0',
                    'Test/SampleTest | <test> was removed | M218'
                ],
                'Major change is detected.'
            ],
            'test-added' => [
                $pathToFixtures . '/test-added/source-code-before',
                $pathToFixtures . '/test-added/source-code-after',
                [
                    'Mftf (MINOR)',
                    'test-added/source-code-after/Magento/TestModule/Test/Mftf/Test/test.xml:0',
                    'Test/NewTest | <test> was added | M237'
                ],
                'Minor change is detected.'
            ],
            'new-module-test-added' => [
                $pathToFixtures . '/new-module-test-added/source-code-before',
                $pathToFixtures . '/new-module-test-added/source-code-after',
                [
                    'Mftf (MINOR)',
                    'new-module-test-added/source-code-after/Magento/TestModuleTwo/Test/Mftf/Test/test.xml:0',
                    'Test/NewTest | <test> was added | M237'
                ],
                'Minor change is detected.'
            ],
            'test-action-changed' => [
                $pathToFixtures . '/test-action-changed/source-code-before',
                $pathToFixtures . '/test-action-changed/source-code-after',
                [
                    'Mftf (PATCH)',
                    'test-action-changed/source-code-before/Magento/TestModule/Test/Mftf/Test/test.xml:0',
                    'Test/SampleTest/key1/userInput | <test> <action> was changed | M222'
                ],
                'Patch change is detected.'
            ],
            'test-action-sequence-changed' => [
                $pathToFixtures . '/test-action-sequence-changed/source-code-before',
                $pathToFixtures . '/test-action-sequence-changed/source-code-after',
                [
                    'Mftf (MAJOR)',
                    'test-action-sequence-changed/source-code-before/Magento/TestModule/Test/Mftf/Test/test.xml:0',
                    'Test/SampleTest | <test> <action> sequence was changed | M223'
                ],
                'Major change is detected.'
            ],
            'test-action-type-changed' => [
                $pathToFixtures . '/test-action-type-changed/source-code-before',
                $pathToFixtures . '/test-action-type-changed/source-code-after',
                [
                    'Mftf (PATCH)',
                    'test-action-type-changed/source-code-before/Magento/TestModule/Test/Mftf/Test/test.xml:0',
                    'Test/SampleTest/action1 | <test> <action> type was changed | M224'
                ],
                'Patch change is detected.'
            ],
            'test-action-removed' => [
                $pathToFixtures . '/test-action-removed/source-code-before',
                $pathToFixtures . '/test-action-removed/source-code-after',
                [
                    'Mftf (MAJOR)',
                    'test-action-removed/source-code-before/Magento/TestModule/Test/Mftf/Test/test.xml:0',
                    'Test/SampleTest/key2 | <test> <action> was removed | M219'
                ],
                'Major change is detected.'
            ],
            'test-action-added' => [
                $pathToFixtures . '/test-action-added/source-code-before',
                $pathToFixtures . '/test-action-added/source-code-after',
                [
                    'Mftf (MINOR)',
                    'test-action-added/source-code-before/Magento/TestModule/Test/Mftf/Test/test.xml:0',
                    'Test/SampleTest/newAction | <test> <action> was added | M238'
                ],
                'Minor change is detected.'
            ],
            'test-before-action-removed' => [
                $pathToFixtures . '/test-before-action-removed/source-code-before',
                $pathToFixtures . '/test-before-action-removed/source-code-after',
                [
                    'Mftf (MAJOR)',
                    'test-before-action-removed/source-code-before/Magento/TestModule/Test/Mftf/Test/test.xml:0',
                    'Test/SampleTest/before/key1 | <test> <action> was removed | M219'
                ],
                'Major change is detected.'
            ],
            'test-before-action-added' => [
                $pathToFixtures . '/test-before-action-added/source-code-before',
                $pathToFixtures . '/test-before-action-added/source-code-after',
                [
                    'Mftf (MINOR)',
                    'test-before-action-added/source-code-before/Magento/TestModule/Test/Mftf/Test/test.xml:0',
                    'Test/SampleTest/before/newAction | <test> <action> was added | M238'
                ],
                'Minor change is detected.'
            ],
            'test-before-action-sequence-changed' => [
                $pathToFixtures . '/test-before-action-sequence-changed/source-code-before',
                $pathToFixtures . '/test-before-action-sequence-changed/source-code-after',
                [
                    'Mftf (MAJOR)',
                    'test-before-action-sequence-changed/source-code-before/Magento/TestModule/Test/Mftf/Test/test.xml:0',
                    'Test/SampleTest/before | <test> <action> sequence was changed | M223'
                ],
                'Major change is detected.'
            ],
            'test-after-action-removed' => [
                $pathToFixtures . '/test-after-action-removed/source-code-before',
                $pathToFixtures . '/test-after-action-removed/source-code-after',
                [
                    'test-after-action-removed/source-code-before/Magento/TestModule/Test/Mftf/Test/test.xml:0',
                    'Mftf (MAJOR)',
                    'Test/SampleTest/after/key1 | <test> <action> was removed | M219'
                ],
                'Major change is detected.'
            ],
            'test-after-action-added' => [
                $pathToFixtures . '/test-after-action-added/source-code-before',
                $pathToFixtures . '/test-after-action-added/source-code-after',
                [
                    'Mftf (MINOR)',
                    'test-after-action-added/source-code-before/Magento/TestModule/Test/Mftf/Test/test.xml:0',
                    'Test/SampleTest/after/newAction | <test> <action> was added | M238'
                ],
                'Minor change is detected.'
            ],
            'test-after-action-sequence-changed' => [
                $pathToFixtures . '/test-after-action-sequence-changed/source-code-before',
                $pathToFixtures . '/test-after-action-sequence-changed/source-code-after',
                [
                    'Mftf (MAJOR)',
                    'test-after-action-sequence-changed/source-code-before/Magento/TestModule/Test/Mftf/Test/test.xml:0',
                    'Test/SampleTest/after | <test> <action> sequence was changed | M223'
                ],
                'Major change is detected.'
            ],
            'test-annotation-changed' => [
                $pathToFixtures . '/test-annotation-changed/source-code-before',
                $pathToFixtures . '/test-annotation-changed/source-code-after',
                [
                    'Mftf (PATCH)',
                    'test-annotation-changed/source-code-before/Magento/TestModule/Test/Mftf/Test/test.xml:0',
                    'Test/SampleTest/annotations/{}description | <test> <annotation> was removed or changed | M221'
                ],
                'Patch change is detected.'
            ],
            'test-group-removed' => [
                $pathToFixtures . '/test-group-removed/source-code-before',
                $pathToFixtures . '/test-group-removed/source-code-after',
                [
                    'Mftf (MAJOR)',
                    'test-group-removed/source-code-before/Magento/TestModule/Test/Mftf/Test/test.xml:0',
                    'Test/SampleTest/annotations/{}group(sampleGroup) | <test> <annotation> <group> was removed | M220'
                ],
                'Major change is detected.'
            ],
            'test-remove-action-added' => [
                $pathToFixtures . '/test-remove-action-added/source-code-before',
                $pathToFixtures . '/test-remove-action-added/source-code-after',
                [
                    'Mftf (MAJOR)',
                    'test-remove-action-added/source-code-before/Magento/TestModule/Test/Mftf/Test/test.xml:0',
                    'Test/SampleTest/newRemoveAction | <test> <remove action> was added | M401'
                ],
                'Major change is detected.'
            ],
            'test-remove-action-removed' => [
                $pathToFixtures . '/test-remove-action-removed/source-code-before',
                $pathToFixtures . '/test-remove-action-removed/source-code-after',
                [
                    'Mftf (MAJOR)',
                    'test-remove-action-removed/source-code-before/Magento/TestModule/Test/Mftf/Test/test.xml:0',
                    'Test/SampleTest/key2 | <test> <remove action> was removed | M402'
                ],
                'Major change is detected.'
            ],
            'test-action-group-ref-changed' => [
                $pathToFixtures . '/test-action-group-ref-changed/source-code-before',
                $pathToFixtures . '/test-action-group-ref-changed/source-code-after',
                [
                    'Mftf (MINOR)',
                    'test-action-group-ref-changed/source-code-before/Magento/TestModule/Test/Mftf/Test/test.xml:0',
                    'Test/SampleTest/key2/ref | <test> <actionGroup> ref was changed | M241'
                ],
                'Minor change is detected.'
            ],
            'suite-added' => [
                $pathToFixtures . '/suite-added/source-code-before',
                $pathToFixtures . '/suite-added/source-code-after',
                [
                    'Mftf (MINOR)',
                    'suite-added/source-code-after/Magento/TestModule/Test/Mftf/Suite/suite.xml:0',
                    'Suite/Sample2Suite | <suite> was added | M407'
                ],
                'Minor change is detected.'
            ],
            'new-module-suite-added' => [
                $pathToFixtures . '/new-module-suite-added/source-code-before',
                $pathToFixtures . '/new-module-suite-added/source-code-after',
                [
                    'Mftf (MINOR)',
                    'new-module-suite-added/source-code-after/Magento/TestModuleTwo/Test/Mftf/Suite/suite.xml:0',
                    'Suite/Sample2Suite | <suite> was added | M407'
                ],
                'Minor change is detected.'
            ],
            'suite-removed' => [
                $pathToFixtures . '/suite-removed/source-code-before',
                $pathToFixtures . '/suite-removed/source-code-after',
                [
                    'Mftf (MAJOR)',
                    'suite-removed/source-code-before/Magento/TestModule/Test/Mftf/Suite/suite.xml:0',
                    'Suite/Sample2Suite | <suite> was removed | M408'
                ],
                'Major change is detected.'
            ],
            'suite-after-action-added' => [
                $pathToFixtures . '/suite-after-action-added/source-code-before',
                $pathToFixtures . '/suite-after-action-added/source-code-after',
                [
                    'Mftf (MINOR)',
                    'suite-after-action-added/source-code-before/Magento/TestModule/Test/Mftf/Suite/suite.xml:0',
                    'Suite/SampleSuite/after/y | <suite> <before/after> <action> was added | M415'
                ],
                'Minor change is detected.'
            ],
            'suite-after-action-changed' => [
                $pathToFixtures . '/suite-after-action-changed/source-code-before',
                $pathToFixtures . '/suite-after-action-changed/source-code-after',
                [
                    'Mftf (PATCH)',
                    'suite-after-action-changed/source-code-before/Magento/TestModule/Test/Mftf/Suite/suite.xml:0',
                    'Suite/SampleSuite/after/x/url | <suite> <before/after> <action> was changed | M416'
                ],
                'Patch change is detected.'
            ],
            'suite-after-action-removed' => [
                $pathToFixtures . '/suite-after-action-removed/source-code-before',
                $pathToFixtures . '/suite-after-action-removed/source-code-after',
                [
                    'Mftf (MAJOR)',
                    'suite-after-action-removed/source-code-before/Magento/TestModule/Test/Mftf/Suite/suite.xml:0',
                    'Suite/SampleSuite/after/y | <suite> <before/after> <action> was removed | M412'
                ],
                'Major change is detected.'
            ],
            'suite-after-action-group-ref-changed' => [
                $pathToFixtures . '/suite-after-action-group-ref-changed/source-code-before',
                $pathToFixtures . '/suite-after-action-group-ref-changed/source-code-after',
                [
                    'Mftf (MINOR)',
                    'suite-after-action-group-ref-changed/source-code-before/Magento/TestModule/Test/Mftf/Suite/suite.xml:0',
                    'Suite/SampleSuite/after/z/ref | <suite> <before/after> <actionGroup> ref was changed | M417'
                ],
                'Minor change is detected.'
            ],
            'suite-after-action-sequence-changed' => [
                $pathToFixtures . '/suite-after-action-sequence-changed/source-code-before',
                $pathToFixtures . '/suite-after-action-sequence-changed/source-code-after',
                [
                    'Mftf (MAJOR)',
                    'suite-after-action-sequence-changed/source-code-before/Magento/TestModule/Test/Mftf/Suite/suite.xml:0',
                    'Suite/SampleSuite/after | <suite> <before/after> <action> sequence was changed | M418'
                ],
                'Major change is detected.'
            ],
            'suite-after-action-type-changed' => [
                $pathToFixtures . '/suite-after-action-type-changed/source-code-before',
                $pathToFixtures . '/suite-after-action-type-changed/source-code-after',
                [
                    'Mftf (PATCH)',
                    'suite-after-action-type-changed/source-code-before/Magento/TestModule/Test/Mftf/Suite/suite.xml:0',
                    'Suite/SampleSuite/after/y | <suite> <before/after> <action> type was changed | M419'
                ],
                'Patch change is detected.'
            ],
            'suite-before-action-added' => [
                $pathToFixtures . '/suite-before-action-added/source-code-before',
                $pathToFixtures . '/suite-before-action-added/source-code-after',
                [
                    'Mftf (MINOR)',
                    'suite-before-action-added/source-code-before/Magento/TestModule/Test/Mftf/Suite/suite.xml:0',
                    'Suite/SampleSuite/before/b | <suite> <before/after> <action> was added | M415'
                ],
                'Minor change is detected.'
            ],
            'suite-before-action-changed' => [
                $pathToFixtures . '/suite-before-action-changed/source-code-before',
                $pathToFixtures . '/suite-before-action-changed/source-code-after',
                [
                    'Mftf (PATCH)',
                    'suite-before-action-changed/source-code-before/Magento/TestModule/Test/Mftf/Suite/suite.xml:0',
                    'Suite/SampleSuite/before/b/userInput | <suite> <before/after> <action> was changed | M416'
                ],
                'Patch change is detected.'
            ],
            'suite-before-action-removed' => [
                $pathToFixtures . '/suite-before-action-removed/source-code-before',
                $pathToFixtures . '/suite-before-action-removed/source-code-after',
                [
                    'Mftf (MAJOR)',
                    'suite-before-action-removed/source-code-before/Magento/TestModule/Test/Mftf/Suite/suite.xml:0',
                    'Suite/SampleSuite/before/b | <suite> <before/after> <action> was removed | M412'
                ],
                'Major change is detected.'
            ],
            'suite-before-action-group-ref-changed' => [
                $pathToFixtures . '/suite-before-action-group-ref-changed/source-code-before',
                $pathToFixtures . '/suite-before-action-group-ref-changed/source-code-after',
                [
                    'Mftf (MINOR)',
                    'suite-before-action-group-ref-changed/source-code-before/Magento/TestModule/Test/Mftf/Suite/suite.xml:0',
                    'Suite/SampleSuite/before/c/ref | <suite> <before/after> <actionGroup> ref was changed | M417'
                ],
                'Minor change is detected.'
            ],
            'suite-before-action-sequence-changed' => [
                $pathToFixtures . '/suite-before-action-sequence-changed/source-code-before',
                $pathToFixtures . '/suite-before-action-sequence-changed/source-code-after',
                [
                    'Mftf (MAJOR)',
                    'suite-before-action-sequence-changed/source-code-before/Magento/TestModule/Test/Mftf/Suite/suite.xml:0',
                    'Suite/SampleSuite/before | <suite> <before/after> <action> sequence was changed | M418'
                ],
                'Major change is detected.'
            ],
            'suite-before-action-type-changed' => [
                $pathToFixtures . '/suite-before-action-type-changed/source-code-before',
                $pathToFixtures . '/suite-before-action-type-changed/source-code-after',
                [
                    'Mftf (PATCH)',
                    'suite-before-action-type-changed/source-code-before/Magento/TestModule/Test/Mftf/Suite/suite.xml:0',
                    'Suite/SampleSuite/before/b | <suite> <before/after> <action> type was changed | M419'
                ],
                'Patch change is detected.'
            ],
            'suite-exclude-added' => [
                $pathToFixtures . '/suite-exclude-added/source-code-before',
                $pathToFixtures . '/suite-exclude-added/source-code-after',
                [
                    'Mftf (PATCH)',
                    'suite-exclude-added/source-code-before/Magento/TestModule/Test/Mftf/Suite/suite.xml:0',
                    'Suite/SampleSuite/exclude/module1 | <suite> <include/exclude> <group/test/module> was added | M409',
                    'Suite/SampleSuite/exclude/test1 | <suite> <include/exclude> <group/test/module> was added | M409',
                ],
                'Patch change is detected.'
            ],
            'suite-exclude-removed' => [
                $pathToFixtures . '/suite-exclude-removed/source-code-before',
                $pathToFixtures . '/suite-exclude-removed/source-code-after',
                [
                    'Mftf (PATCH)',
                    'suite-exclude-removed/source-code-before/Magento/TestModule/Test/Mftf/Suite/suite.xml:0',
                    'Suite/SampleSuite/exclude/module1 | <suite> <include/exclude> <group/test/module> was removed | M410'
                ],
                'Patch change is detected.'
            ],
            'suite-include-added' => [
                $pathToFixtures . '/suite-include-added/source-code-before',
                $pathToFixtures . '/suite-include-added/source-code-after',
                [
                    'Mftf (PATCH)',
                    'suite-include-added/source-code-before/Magento/TestModule/Test/Mftf/Suite/suite.xml:0',
                    'Suite/SampleSuite/include/module1 | <suite> <include/exclude> <group/test/module> was added | M409',
                    'Suite/SampleSuite/include/test1 | <suite> <include/exclude> <group/test/module> was added | M409',
                ],
                'Patch change is detected.'
            ],
            'suite-include-removed' => [
                $pathToFixtures . '/suite-include-removed/source-code-before',
                $pathToFixtures . '/suite-include-removed/source-code-after',
                [
                    'Mftf (PATCH)',
                    'suite-include-removed/source-code-before/Magento/TestModule/Test/Mftf/Suite/suite.xml:0',
                    'Suite/SampleSuite/include/module1 | <suite> <include/exclude> <group/test/module> was removed | M410'
                ],
                'Patch change is detected.'
            ],
            'suite-include-changed' => [
                $pathToFixtures . '/suite-include-changed/source-code-before',
                $pathToFixtures . '/suite-include-changed/source-code-after',
                [
                    'Mftf (PATCH)',
                    'suite-include-changed/source-code-before/Magento/TestModule/Test/Mftf/Suite/suite.xml:0',
                    'Suite/SampleSuite/include/group1 | <suite> <include/exclude> <group/test/module> was removed | M410',
                    'Suite/SampleSuite/include/group2 | <suite> <include/exclude> <group/test/module> was added | M409',
                ],
                'Patch change is detected.'
            ],
            'suite-exclude-changed' => [
                $pathToFixtures . '/suite-exclude-changed/source-code-before',
                $pathToFixtures . '/suite-exclude-changed/source-code-after',
                [
                    'Mftf (PATCH)',
                    'suite-exclude-changed/source-code-before/Magento/TestModule/Test/Mftf/Suite/suite.xml:0',
                    'Suite/SampleSuite/exclude/group1 | <suite> <include/exclude> <group/test/module> was removed | M410',
                    'Suite/SampleSuite/exclude/group2 | <suite> <include/exclude> <group/test/module> was added | M409',
                ],
                'Patch change is detected.'
            ],
            'suite-after-remove-action-added' => [
                $pathToFixtures . '/suite-after-remove-action-added/source-code-before',
                $pathToFixtures . '/suite-after-remove-action-added/source-code-after',
                [
                    'Mftf (MAJOR)',
                    'suite-after-remove-action-added/source-code-before/Magento/TestModule/Test/Mftf/Suite/suite.xml:0',
                    'Suite/SampleSuite/after/x | <suite> <before/after> <remove> <action> was added | M420'
                ],
                'Major change is detected.'
            ],
            'suite-after-remove-action-removed' => [
                $pathToFixtures . '/suite-after-remove-action-removed/source-code-before',
                $pathToFixtures . '/suite-after-remove-action-removed/source-code-after',
                [
                    'Mftf (MAJOR)',
                    'suite-after-remove-action-removed/source-code-before/Magento/TestModule/Test/Mftf/Suite/suite.xml:0',
                    'Suite/SampleSuite/after/x | <suite> <before/after> <remove> <action> was removed | M421'
                ],
                'Major change is detected.'
            ],
            'suite-before-remove-action-added' => [
                $pathToFixtures . '/suite-before-remove-action-added/source-code-before',
                $pathToFixtures . '/suite-before-remove-action-added/source-code-after',
                [
                    'Mftf (MAJOR)',
                    'suite-before-remove-action-added/source-code-before/Magento/TestModule/Test/Mftf/Suite/suite.xml:0',
                    'Suite/SampleSuite/before/x | <suite> <before/after> <remove> <action> was added | M420'
                ],
                'Major change is detected.'
            ],
            'suite-before-remove-action-removed' => [
                $pathToFixtures . '/suite-before-remove-action-removed/source-code-before',
                $pathToFixtures . '/suite-before-remove-action-removed/source-code-after',
                [
                    'Mftf (MAJOR)',
                    'suite-before-remove-action-removed/source-code-before/Magento/TestModule/Test/Mftf/Suite/suite.xml:0',
                    'Suite/SampleSuite/before/x | <suite> <before/after> <remove> <action> was removed | M421'
                ],
                'Major change is detected.'
            ],
            'actionGroup-remove-action-key-changed' => [
                $pathToFixtures . '/actionGroup-remove-action-key-changed/source-code-before',
                $pathToFixtures . '/actionGroup-remove-action-key-changed/source-code-after',
                [
                    'Mftf (MAJOR)',
                    'actionGroup-remove-action-key-changed/source-code-before/Magento/TestModule/Test/Mftf/ActionGroup/actionGroup.xml:0',
                    'ActionGroup/ActionGroup1/action2 | <actionGroup> <remove action> was removed | M406',
                    'ActionGroup/ActionGroup1/action1 | <actionGroup> <remove action> was added | M404',
                ],
                'Major change is detected.'
            ],
            'suite-before-remove-action-key-changed' => [
                $pathToFixtures . '/suite-before-remove-action-key-changed/source-code-before',
                $pathToFixtures . '/suite-before-remove-action-key-changed/source-code-after',
                [
                    'Mftf (MAJOR)',
                    'suite-before-remove-action-key-changed/source-code-before/Magento/TestModule/Test/Mftf/Suite/suite.xml:0',
                    'Suite/SampleSuite/before/a | <suite> <before/after> <remove> <action> was removed | M421',
                    'Suite/SampleSuite/before/b | <suite> <before/after> <remove> <action> was added | M420',
                ],
                'Major change is detected.'
            ],
            'suite-after-remove-action-key-changed' => [
                $pathToFixtures . '/suite-after-remove-action-key-changed/source-code-before',
                $pathToFixtures . '/suite-after-remove-action-key-changed/source-code-after',
                [
                    'Mftf (MAJOR)',
                    ' suite-after-remove-action-key-changed/source-code-before/Magento/TestModule/Test/Mftf/Suite/suite.xml:0',
                    'Suite/SampleSuite/after/a | <suite> <before/after> <remove> <action> was removed | M421',
                    'Suite/SampleSuite/after/b | <suite> <before/after> <remove> <action> was added | M420',
                ],
                'Major change is detected.'
            ],
            'test-remove-action-key-changed' => [
                $pathToFixtures . '/test-remove-action-key-changed/source-code-before',
                $pathToFixtures . '/test-remove-action-key-changed/source-code-after',
                [
                    'Mftf (MAJOR)',
                    'test-remove-action-key-changed/source-code-before/Magento/TestModule/Test/Mftf/Test/test.xml:0',
                    'Test/SampleTest/key2 | <test> <remove action> was removed | M402',
                    'Test/SampleTest/key1 | <test> <remove action> was added | M401',
                ],
                'Major change is detected.'
            ],
        ];
    }
}
