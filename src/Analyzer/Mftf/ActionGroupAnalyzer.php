<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Analyzer\Mftf;

use Magento\SemanticVersionChecker\Analyzer\AnalyzerInterface;
use Magento\SemanticVersionChecker\MftfReport;
use Magento\SemanticVersionChecker\Operation\Mftf\ActionGroup\ActionGroupActionAdded;
use Magento\SemanticVersionChecker\Operation\Mftf\ActionGroup\ActionGroupActionChanged;
use Magento\SemanticVersionChecker\Operation\Mftf\ActionGroup\ActionGroupActionRemoved;
use Magento\SemanticVersionChecker\Operation\Mftf\ActionGroup\ActionGroupActionTypeChanged;
use Magento\SemanticVersionChecker\Operation\Mftf\ActionGroup\ActionGroupAdded;
use Magento\SemanticVersionChecker\Operation\Mftf\ActionGroup\ActionGroupArgumentAdded;
use Magento\SemanticVersionChecker\Operation\Mftf\ActionGroup\ActionGroupArgumentChanged;
use Magento\SemanticVersionChecker\Operation\Mftf\ActionGroup\ActionGroupArgumentRemoved;
use Magento\SemanticVersionChecker\Operation\Mftf\ActionGroup\ActionGroupRemoved;
use Magento\SemanticVersionChecker\Registry\XmlRegistry;
use Magento\SemanticVersionChecker\Scanner\MftfScanner;
use PHPSemVerChecker\Report\Report;
use Magento\SemanticVersionChecker\Operation\Mftf\ActionGroup\ActionGroupRemoveActionRemoved;
use Magento\SemanticVersionChecker\Operation\Mftf\ActionGroup\ActionGroupRemoveActionAdded;

/**
 * Analyzer for Mftf Action Groups.
 */
class ActionGroupAnalyzer extends AbstractEntityAnalyzer implements AnalyzerInterface
{
    public const MFTF_ARGUMENTS_ELEMENT = "{}arguments";
    public const MFTF_DATA_TYPE = 'actionGroup';

    /**
     * operations array
     *
     * @var string[][]
     */
    private static $operations = [
        'stepKey' => [
            'add' => ActionGroupActionAdded::class,
            'remove' => ActionGroupActionRemoved::class,
        ],
        'keyForRemoval' => [
            'add' => ActionGroupRemoveActionAdded::class,
            'remove' => ActionGroupRemoveActionRemoved::class,
        ],
    ];

    /**
     * MFTF ActionGroup.xml analyzer.
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
                ActionGroupAdded::class,
                $module . '/ActionGroup'
            );
            foreach ($entities as $entityName => $beforeEntity) {
                if ($beforeEntity['type'] !== self::MFTF_DATA_TYPE) {
                    continue;
                }
                $operationTarget = $module . '/ActionGroup/' . $entityName;
                $filenames = implode(", ", $beforeEntity['filePaths']);

                // Validate section still exists
                if (!isset($afterEntities[$module][$entityName])) {
                    $operation = new ActionGroupRemoved($filenames, $operationTarget);
                    $this->getReport()->add(MftfReport::MFTF_REPORT_CONTEXT, $operation);
                    continue;
                }

                //Sorted before Elements
                $beforeArguments = [];
                $beforeActions = [];
                $afterArguments = [];
                $afterActions = [];

                foreach ($beforeEntity['value'] ?? [] as $beforeChild) {
                    if ($beforeChild['name'] == self::MFTF_ARGUMENTS_ELEMENT) {
                        $beforeArguments = $beforeChild['value'];
                    } else {
                        $beforeActions[] = $beforeChild;
                    }
                }

                foreach ($afterEntities[$module][$entityName]['value'] ?? [] as $afterChild) {
                    if ($afterChild['name'] == self::MFTF_ARGUMENTS_ELEMENT) {
                        $afterArguments = $afterChild['value'];
                    } else {
                        $afterActions[] = $afterChild;
                    }
                }

                // Validate <actions>
                foreach ($beforeActions as $testAction) {
                    if (isset($testAction['attributes']['stepKey'])) {
                        $elementIdentifier = 'stepKey';
                    } elseif (isset($testAction['attributes']['keyForRemoval'])) {
                        $elementIdentifier = 'keyForRemoval';
                    } else {
                        continue;
                    }

                    $beforeFieldKey = $testAction['attributes'][$elementIdentifier];
                    $matchingElement = $this->findMatchingElement(
                        $testAction,
                        $afterActions,
                        $elementIdentifier
                    );
                    if ($matchingElement === null) {
                        $operation = new self::$operations[$elementIdentifier]['remove'](
                            $filenames,
                            $operationTarget . '/' . $beforeFieldKey
                        );
                        $this->getReport()->add(MftfReport::MFTF_REPORT_CONTEXT, $operation);
                    } else {
                        $this->matchAndValidateAttributes(
                            $testAction['attributes'],
                            $matchingElement['attributes'],
                            $this->getReport(),
                            $filenames,
                            [AbstractEntityAnalyzer::DEFAULT_OPERATION_KEY => ActionGroupActionChanged::class],
                            "$operationTarget/$beforeFieldKey"
                        );
                        $this->matchAndValidateElementType(
                            $testAction,
                            $matchingElement,
                            $this->getReport(),
                            $filenames,
                            ActionGroupActionTypeChanged::class,
                            "$operationTarget/$beforeFieldKey"
                        );
                    }
                }
                foreach (self::$operations as $identifier => $operations) {
                    $this->findAddedElementsInArray(
                        $beforeActions,
                        $afterActions,
                        $identifier,
                        $this->getReport(),
                        $filenames,
                        $operations['add'],
                        $operationTarget
                    );
                }

                // Validate <arguments>
                if (is_array($beforeArguments) || is_object($beforeArguments)) {
                    foreach ($beforeArguments as $argument) {
                        $beforeFieldKey = $argument['attributes']['name'];
                        $matchingElement = $this->findMatchingElement($argument, $afterArguments, 'name');
                        if ($matchingElement === null) {
                            $operation = new ActionGroupArgumentRemoved(
                                $filenames,
                                $operationTarget . '/Arguments/' . $beforeFieldKey
                            );
                            $this->getReport()->add(MftfReport::MFTF_REPORT_CONTEXT, $operation);
                        } else {
                            $this->matchAndValidateAttributes(
                                $argument['attributes'],
                                $matchingElement['attributes'],
                                $this->getReport(),
                                $filenames,
                                [AbstractEntityAnalyzer::DEFAULT_OPERATION_KEY => ActionGroupArgumentChanged::class],
                                "$operationTarget/$beforeFieldKey"
                            );
                        }
                    }
                }

                $this->findAddedElementsInArray(
                    $beforeArguments,
                    $afterArguments,
                    'name',
                    $this->getReport(),
                    $filenames,
                    ActionGroupArgumentAdded::class,
                    $operationTarget
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
                ActionGroupAdded::class,
                $module . '/ActionGroup'
            );
        }
        return $this->getReport();
    }
}
