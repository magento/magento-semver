<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Analyzer\Mftf;

use Magento\SemanticVersionChecker\MftfReport;
use Magento\SemanticVersionChecker\Operation\Mftf\Suite\SuiteIncludeExcludeAdded;
use Magento\SemanticVersionChecker\Operation\Mftf\Suite\SuiteIncludeExcludeRemoved;
use Magento\SemanticVersionChecker\Operation\Mftf\Suite\SuiteBeforeAfterActionChanged;
use Magento\SemanticVersionChecker\Operation\Mftf\Suite\SuiteBeforeAfterActionGroupRefChanged;
use Magento\SemanticVersionChecker\Operation\Mftf\Suite\SuiteBeforeAfterActionAdded;
use Magento\SemanticVersionChecker\Operation\Mftf\Suite\SuiteBeforeAfterActionRemoved;
use Magento\SemanticVersionChecker\Operation\Mftf\Suite\SuiteBeforeAfterActionSequenceChanged;
use Magento\SemanticVersionChecker\Operation\Mftf\Suite\SuiteBeforeAfterActionTypeChanged;
use Magento\SemanticVersionChecker\Operation\Mftf\Suite\SuiteBeforeAfterRemoveActionAdded;
use Magento\SemanticVersionChecker\Operation\Mftf\Suite\SuiteBeforeAfterRemoveActionRemoved;
use Magento\SemanticVersionChecker\Operation\Mftf\Suite\SuiteAdded;
use Magento\SemanticVersionChecker\Operation\Mftf\Suite\SuiteRemoved;
use Magento\SemanticVersionChecker\Scanner\MftfScanner;
use PHPSemVerChecker\Registry\Registry;
use PHPSemVerChecker\Report\Report;

class SuiteAnalyzer extends AbstractEntityAnalyzer
{
    const MFTF_SUITE_BEFORE_ELEMENT = "{}before";
    const MFTF_SUITE_AFTER_ELEMENT = "{}after";
    const MFTF_SUITE_INCLUDE_ELEMENT = "{}include";
    const MFTF_SUITE_EXCLUDE_ELEMENT = "{}exclude";
    const MFTF_DATA_TYPE = 'suite';
    const MFTF_DATA_DIRECTORY = '/Mftf/Suite/';

    /**
     * Action operations array
     *
     * @var string[][]
     */
    private static $operations = [
        'stepKey' => [
            'add' => SuiteBeforeAfterActionAdded::class,
            'remove' => SuiteBeforeAfterActionRemoved::class,
        ],
        'keyForRemoval' => [
            'add' => SuiteBeforeAfterRemoveActionAdded::class,
            'remove' => SuiteBeforeAfterRemoveActionRemoved::class,
        ],
    ];

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
                SuiteAdded::class,
                $module . '/Suite'
            );
            foreach ($entities as $entityName => $beforeEntity) {
                if ($beforeEntity['type'] !== self::MFTF_DATA_TYPE) {
                    continue;
                }
                $operationTarget = $module . '/Suite/' . $entityName;
                $filenames = implode(", ", $beforeEntity['filePaths']);

                // Validate suite still exists
                if (!isset($afterEntities[$module][$entityName])) {
                    $operation = new SuiteRemoved($filenames, $operationTarget);
                    $this->getReport()->add(MftfReport::MFTF_REPORT_CONTEXT, $operation);
                    continue;
                }

                //Sort Elements
                $beforeSuiteBeforeActions = [];
                $beforeSuiteAfterActions = [];
                $beforeSuiteIncludeElements = [];
                $beforeSuiteExcludeElements = [];

                $afterSuiteBeforeActions = [];
                $afterSuiteAfterActions = [];
                $afterSuiteIncludeElements = [];
                $afterSuiteExcludeElements = [];

                foreach ($beforeEntity['value'] as $beforeChild) {
                    if ($beforeChild['name'] == self::MFTF_SUITE_BEFORE_ELEMENT) {
                        $beforeSuiteBeforeActions = $beforeChild['value'] ?? [];
                    } elseif ($beforeChild['name'] == self::MFTF_SUITE_AFTER_ELEMENT) {
                        $beforeSuiteAfterActions = $beforeChild['value'] ?? [];
                    } elseif ($beforeChild['name'] == self::MFTF_SUITE_INCLUDE_ELEMENT) {
                        $beforeSuiteIncludeElements = $beforeChild['value'] ?? [];
                    } elseif ($beforeChild['name'] == self::MFTF_SUITE_EXCLUDE_ELEMENT) {
                        $beforeSuiteExcludeElements = $beforeChild['value'] ?? [];
                    }
                }
                foreach ($afterEntities[$module][$entityName]['value'] as $afterChild) {
                    if ($afterChild['name'] == self::MFTF_SUITE_BEFORE_ELEMENT) {
                        $afterSuiteBeforeActions = $afterChild['value'] ?? [];
                    } elseif ($afterChild['name'] == self::MFTF_SUITE_AFTER_ELEMENT) {
                        $afterSuiteAfterActions = $afterChild['value'] ?? [];
                    } elseif ($afterChild['name'] == self::MFTF_SUITE_INCLUDE_ELEMENT) {
                        $afterSuiteIncludeElements = $afterChild['value'] ?? [];
                    } elseif ($afterChild['name'] == self::MFTF_SUITE_EXCLUDE_ELEMENT) {
                        $afterSuiteExcludeElements = $afterChild['value'] ?? [];
                    }
                }

                // Validate <before> <action> elements
                $this->validateActionsInBlock(
                    $beforeSuiteBeforeActions,
                    $afterSuiteBeforeActions,
                    $this->getReport(),
                    $filenames,
                    $operationTarget . "/before"
                );

                // Validate <after> <action> elements
                $this->validateActionsInBlock(
                    $beforeSuiteAfterActions,
                    $afterSuiteAfterActions,
                    $this->getReport(),
                    $filenames,
                    $operationTarget . "/after"
                );

                // Validate <include> elements
                $this->validateIncludesExcludes(
                    $beforeSuiteIncludeElements,
                    $afterSuiteIncludeElements,
                    $this->getReport(),
                    $filenames,
                    $operationTarget . "/include"
                );

                // Validate <exclude> elements
                $this->validateIncludesExcludes(
                    $beforeSuiteExcludeElements,
                    $afterSuiteExcludeElements,
                    $this->getReport(),
                    $filenames,
                    $operationTarget . "/exclude"
                );
            }
        }
        return $this->getReport();
    }

    /**
     * Validates all actions in given test block
     *
     * @param array  $beforeTestActions
     * @param array  $afterTestActions
     * @param Report $report
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
            SuiteBeforeAfterActionSequenceChanged::class,
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
                        AbstractEntityAnalyzer::DEFAULT_OPERATION_KEY => SuiteBeforeAfterActionChanged::class,
                        'ref' => SuiteBeforeAfterActionGroupRefChanged::class,
                    ],
                    "$operationTarget/$beforeFieldKey"
                );
                $this->matchAndValidateElementType(
                    $testAction,
                    $matchingElement,
                    $report,
                    $filenames,
                    SuiteBeforeAfterActionTypeChanged::class,
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

    /**
     * Validate includes and excludes elements
     *
     * @param array  $beforeElements
     * @param array  $afterElements
     * @param Report $report
     * @param string $filenames
     * @param string $operationTarget
     * @return void
     */
    public function validateIncludesExcludes(
        $beforeElements,
        $afterElements,
        $report,
        $filenames,
        $operationTarget
    ) {
        foreach ($beforeElements as $beforeElement) {
            $elementIdentifier = 'name';
            if (!isset($beforeElement['attributes'][$elementIdentifier])) {
                continue;
            }
            $beforeElementKey = $beforeElement['attributes'][$elementIdentifier];
            $matchingElement = $this->findMatchingElement($beforeElement, $afterElements, $elementIdentifier);

            if ($matchingElement === null) {
                $operation = new SuiteIncludeExcludeRemoved($filenames, "$operationTarget/$beforeElementKey");
                $report->add(MftfReport::MFTF_REPORT_CONTEXT, $operation);
            }
        }

        foreach ($afterElements as $afterElement) {
            $elementIdentifier = 'name';
            if (!isset($afterElement['attributes'][$elementIdentifier])) {
                continue;
            }
            $afterElementKey = $afterElement['attributes'][$elementIdentifier];
            $matchingElement = $this->findMatchingElement($afterElement, $beforeElements, $elementIdentifier);

            if ($matchingElement === null) {
                $operation = new SuiteIncludeExcludeAdded($filenames, "$operationTarget/$afterElementKey");
                $report->add(MftfReport::MFTF_REPORT_CONTEXT, $operation);
            }
        }
    }
}
