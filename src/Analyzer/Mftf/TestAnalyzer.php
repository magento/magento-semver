<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Analyzer\Mftf;

use Magento\SemanticVersionChecker\MftfReport;
use Magento\SemanticVersionChecker\Operation\Mftf\Test\TestActionAdded;
use Magento\SemanticVersionChecker\Operation\Mftf\Test\TestActionChanged;
use Magento\SemanticVersionChecker\Operation\Mftf\Test\TestActionRemove;
use Magento\SemanticVersionChecker\Operation\Mftf\Test\TestActionTypeChanged;
use Magento\SemanticVersionChecker\Operation\Mftf\Test\TestAdded;
use Magento\SemanticVersionChecker\Operation\Mftf\Test\TestAnnotationAdded;
use Magento\SemanticVersionChecker\Operation\Mftf\Test\TestAnnotationChanged;
use Magento\SemanticVersionChecker\Operation\Mftf\Test\TestGroupRemove;
use Magento\SemanticVersionChecker\Operation\Mftf\Test\TestRemove;
use Magento\SemanticVersionChecker\Scanner\MftfScanner;
use PHPSemVerChecker\Registry\Registry;
use PHPSemVerChecker\Report\Report;

class TestAnalyzer extends AbstractEntityAnalyzer
{
    const MFTF_ANOTATION_ELEMENT = "{}annotations";
    const MFTF_BEFORE_ELEMENT = "{}before";
    const MFTF_AFTER_ELEMENT = "{}after";
    const MFTF_GROUP_ELEMENT = "{}group";
    const MFTF_DATA_TYPE = 'test';
    const MFTF_DATA_DIRECTORY = '/Mftf/Test/';

    /**
     * MFTF test.xml analyzer
     *
     * @param Registry $registryBefore
     * @param Registry $registryAfter
     * @return Report
     */
    public function analyze(Registry $registryBefore, Registry $registryAfter)
    {
        $beforeEntities = $registryBefore->data[MftfScanner::MFTF_ENTITY] ?? [];
        $afterEntities = $registryAfter->data[MftfScanner::MFTF_ENTITY] ?? [];

        foreach ($beforeEntities as $module => $entities) {
            $this->findAddedEntitiesInModule(
                $entities,
                $afterEntities[$module] ?? [],
                self::MFTF_DATA_TYPE,
                $this->getReport(),
                TestAdded::class,
                $module . '/Test'
            );
            foreach ($entities as $entityName => $beforeEntity) {
                if ($beforeEntity['type'] !== self::MFTF_DATA_TYPE) {
                    continue;
                }
                $operationTarget = $module . '/Test/' . $entityName;
                $filenames = implode(", ", $beforeEntity['filePaths']);

                // Validate test still exists
                if (!isset($afterEntities[$module][$entityName])) {
                    $operation = new TestRemove($filenames, $operationTarget);
                    $this->getReport()->add(MftfReport::MFTF_REPORT_CONTEXT, $operation);
                    continue;
                }

                //Sort Elements
                $beforeAnnotations = null;
                $beforeTestBefore = null;
                $beforeTestAfter = null;
                $beforeTestActions = [];

                $afterAnnotations = null;
                $afterTestBefore = null;
                $afterTestAfter = null;
                $afterTestActions = [];

                foreach ($beforeEntity['value'] as $beforeChild) {
                    if ($beforeChild['name'] == self::MFTF_ANOTATION_ELEMENT) {
                        $beforeAnnotations = $beforeChild;
                    } elseif ($beforeChild['name'] == self::MFTF_BEFORE_ELEMENT) {
                        $beforeTestBefore = $beforeChild;
                    } elseif ($beforeChild['name'] == self::MFTF_AFTER_ELEMENT) {
                        $beforeTestAfter = $beforeChild;
                    } else {
                        $beforeTestActions[] = $beforeChild;
                    }
                }
                foreach ($afterEntities[$module][$entityName]['value'] as $afterChild) {
                    if ($afterChild['name'] == self::MFTF_ANOTATION_ELEMENT) {
                        $afterAnnotations = $afterChild;
                    } elseif ($afterChild['name'] == self::MFTF_BEFORE_ELEMENT) {
                        $afterTestBefore = $afterChild;
                    } elseif ($afterChild['name'] == self::MFTF_AFTER_ELEMENT) {
                        $afterTestAfter = $afterChild;
                    } else {
                        $afterTestActions[] = $afterChild;
                    }
                }

                // Validate removal of group <annotation>
                foreach ($beforeAnnotations['value'] ?? [] as $annotation) {
                    if (!isset($annotation['attributes']['value'])) {
                        continue;
                    }
                    $beforeFieldKey = $annotation['attributes']['value'];
                    $beforeFieldName = $annotation['name'];
                    $matchingElement = $this->findMatchingElement(
                        $annotation,
                        $afterAnnotations['value'],
                        'value'
                    );
                    if ($annotation['name'] == self::MFTF_GROUP_ELEMENT && $matchingElement === null) {
                        $operation = new TestGroupRemove(
                            $filenames,
                            "$operationTarget/annotations/$beforeFieldName($beforeFieldKey)"
                        );
                        $this->getReport()->add(MftfReport::MFTF_REPORT_CONTEXT, $operation);
                    } elseif ($matchingElement === null) {
                        $operation = new TestAnnotationChanged(
                            $filenames,
                            "$operationTarget/annotations/$beforeFieldName"
                        );
                        $this->getReport()->add(MftfReport::MFTF_REPORT_CONTEXT, $operation);
                    }
                }

                // Validate <action> elements
                $this->validateActionsInBlock(
                    $beforeTestActions,
                    $afterTestActions,
                    $this->getReport(),
                    $filenames,
                    $operationTarget
                );
                // Validate <before><action> elements
                $this->validateActionsInBlock(
                    $beforeTestBefore['value'] ?? [],
                    $afterTestBefore['value'] ?? [],
                    $this->getReport(),
                    $filenames,
                    $operationTarget . "/before"
                );
                // Validate <after><action> elements
                $this->validateActionsInBlock(
                    $beforeTestAfter['value'] ?? [],
                    $afterTestAfter['value'] ?? [],
                    $this->getReport(),
                    $filenames,
                    $operationTarget . "/after"
                );
            }
        }
        return $this->getReport();
    }

    /**
     * Validates all actions in given test block
     *
     * @param array $beforeTestActions
     * @param array $afterTestActions
     * @param Report$report
     * @param string $filenames
     * @param string $operationTarget
     * @return void
     */
    public function validateActionsInBlock(
        $beforeTestActions,
        $afterTestActions,
        $report,
        $filenames,
        $operationTarget
    ) {
        foreach ($beforeTestActions as $testAction) {
            $beforeFieldKey = $testAction['attributes']['stepKey'];
            $matchingElement = $this->findMatchingElement($testAction, $afterTestActions,'stepKey');
            if ($matchingElement === null) {
                $operation = new TestActionRemove($filenames, "$operationTarget/$beforeFieldKey");
                $report->add(MftfReport::MFTF_REPORT_CONTEXT, $operation);
            } else {
                $this->matchAndValidateAttributes(
                    $testAction['attributes'],
                    $matchingElement['attributes'],
                    $report,
                    $filenames,
                    TestActionChanged::class,
                    "$operationTarget/$beforeFieldKey"
                );
                $this->matchAndValidateElementType(
                    $testAction,
                    $matchingElement,
                    $report,
                    $filenames,
                    TestActionTypeChanged::class,
                    "$operationTarget/$beforeFieldKey"
                );
            }
        }
        $this->findAddedElementsInArray(
            $beforeTestActions,
            $afterTestActions,
            'stepKey',
            $report,
            $filenames,
            TestActionAdded::class,
            $operationTarget
        );
    }
}
