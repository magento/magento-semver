<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Analyzer\Mftf;

use Magento\SemanticVersionChecker\Analyzer\AnalyzerInterface;
use Magento\SemanticVersionChecker\MftfReport;
use Magento\SemanticVersionChecker\Operation\Mftf\Test\TestActionAdded;
use Magento\SemanticVersionChecker\Operation\Mftf\Test\TestActionChanged;
use Magento\SemanticVersionChecker\Operation\Mftf\Test\TestActionGroupRefChanged;
use Magento\SemanticVersionChecker\Operation\Mftf\Test\TestActionRemoved;
use Magento\SemanticVersionChecker\Operation\Mftf\Test\TestActionSequenceChanged;
use Magento\SemanticVersionChecker\Operation\Mftf\Test\TestActionTypeChanged;
use Magento\SemanticVersionChecker\Operation\Mftf\Test\TestAdded;
use Magento\SemanticVersionChecker\Operation\Mftf\Test\TestAnnotationChanged;
use Magento\SemanticVersionChecker\Operation\Mftf\Test\TestGroupRemoved;
use Magento\SemanticVersionChecker\Operation\Mftf\Test\TestRemoved;
use Magento\SemanticVersionChecker\Registry\XmlRegistry;
use Magento\SemanticVersionChecker\Scanner\MftfScanner;
use PHPSemVerChecker\Report\Report;
use Magento\SemanticVersionChecker\Operation\Mftf\Test\TestRemoveActionRemoved;
use Magento\SemanticVersionChecker\Operation\Mftf\Test\TestRemoveActionAdded;

/**
 * Mftf Test analyzer class.
 */
class TestAnalyzer extends AbstractEntityAnalyzer implements AnalyzerInterface
{
    public const MFTF_ANOTATION_ELEMENT = "{}annotations";
    public const MFTF_BEFORE_ELEMENT = "{}before";
    public const MFTF_AFTER_ELEMENT = "{}after";
    public const MFTF_GROUP_ELEMENT = "{}group";
    public const MFTF_DATA_TYPE = 'test';

    /**
     * operations array
     *
     * @var string[][]
     */
    private static $operations = [
        'stepKey' => [
            'add' => TestActionAdded::class,
            'remove' => TestActionRemoved::class,
        ],
        'keyForRemoval' => [
            'add' => TestRemoveActionAdded::class,
            'remove' => TestRemoveActionRemoved::class,
        ],
    ];

    /**
     * MFTF test.xml analyzer
     *
     * @param XmlRegistry $registryBefore
     * @param XmlRegistry $registryAfter
     * @return Report
     */
    public function analyze($registryBefore, $registryAfter)
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
                    $operation = new TestRemoved($filenames, $operationTarget);
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
                        $operation = new TestGroupRemoved(
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

        // check new modules
        $newModuleEntities = array_diff_key($afterEntities, $beforeEntities);
        foreach ($newModuleEntities as $module => $entities) {
            $this->findAddedEntitiesInModule(
                $beforeEntities[$module] ?? [],
                $entities,
                self::MFTF_DATA_TYPE,
                $this->getReport(),
                TestAdded::class,
                $module . '/Test'
            );
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
        $this->matchAndValidateActionsSequence(
            $beforeTestActions,
            $afterTestActions,
            $report,
            $filenames,
            TestActionSequenceChanged::class,
            $operationTarget
        );

        foreach ($beforeTestActions as $testAction) {
            if (isset($testAction['attributes']['stepKey'])) {
                $elementIdentifier = 'stepKey';
            } elseif (isset($testAction['attributes']['keyForRemoval'])) {
                $elementIdentifier = 'keyForRemoval';
            } else {
                continue;
            }

            $beforeFieldKey = $testAction['attributes'][$elementIdentifier];
            $matchingElement = $this->findMatchingElement($testAction, $afterTestActions, $elementIdentifier);
            if ($matchingElement === null) {
                $operation = new self::$operations[$elementIdentifier]['remove'](
                    $filenames,
                    "$operationTarget/$beforeFieldKey"
                );
                $report->add(MftfReport::MFTF_REPORT_CONTEXT, $operation);
            } else {
                $this->matchAndValidateAttributes(
                    $testAction['attributes'],
                    $matchingElement['attributes'],
                    $report,
                    $filenames,
                    [
                        AbstractEntityAnalyzer::DEFAULT_OPERATION_KEY => TestActionChanged::class,
                        'ref' => TestActionGroupRefChanged::class,
                    ],
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

        foreach (self::$operations as $identifier => $operations) {
            $this->findAddedElementsInArray(
                $beforeTestActions,
                $afterTestActions,
                $identifier,
                $report,
                $filenames,
                $operations['add'],
                $operationTarget
            );
        }
    }
}
