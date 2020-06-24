<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Analyzer\Mftf;

use Magento\SemanticVersionChecker\MftfReport;
use Magento\SemanticVersionChecker\Operation\Mftf\ActionGroup\ActionGroupActionAdded;
use Magento\SemanticVersionChecker\Operation\Mftf\ActionGroup\ActionGroupActionChanged;
use Magento\SemanticVersionChecker\Operation\Mftf\ActionGroup\ActionGroupActionRemove;
use Magento\SemanticVersionChecker\Operation\Mftf\ActionGroup\ActionGroupActionTypeChanged;
use Magento\SemanticVersionChecker\Operation\Mftf\ActionGroup\ActionGroupAdded;
use Magento\SemanticVersionChecker\Operation\Mftf\ActionGroup\ActionGroupArgumentAdded;
use Magento\SemanticVersionChecker\Operation\Mftf\ActionGroup\ActionGroupArgumentChanged;
use Magento\SemanticVersionChecker\Operation\Mftf\ActionGroup\ActionGroupArgumentRemove;
use Magento\SemanticVersionChecker\Operation\Mftf\ActionGroup\ActionGroupRemove;
use Magento\SemanticVersionChecker\Scanner\MftfScanner;
use PHPSemVerChecker\Registry\Registry;
use PHPSemVerChecker\Report\Report;

class ActionGroupAnalyzer extends AbstractEntityAnalyzer
{
    const MFTF_ARGUMENTS_ELEMENT = "{}arguments";
    const MFTF_DATA_TYPE = 'actionGroup';
    const MFTF_DATA_DIRECTORY = '/Mftf/ActionGroup/';

    /**
     * MFTF actionGroup.xml analyzer
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
                    $operation = new ActionGroupRemove($filenames, $operationTarget);
                    $this->getReport()->add(MftfReport::MFTF_REPORT_CONTEXT, $operation);
                    continue;
                }

                //Sorted before Elements
                $beforeArguments = [];
                $beforeActions = [];
                $afterArguments = [];
                $afterActions = [];

                foreach ($beforeEntity['value'] as $beforeChild) {
                    if ($beforeChild['name'] == self::MFTF_ARGUMENTS_ELEMENT) {
                        $beforeArguments = $beforeChild['value'];
                    } else {
                        $beforeActions[] = $beforeChild;
                    }
                }
                foreach ($afterEntities[$module][$entityName]['value'] as $afterChild) {
                    if ($afterChild['name'] == self::MFTF_ARGUMENTS_ELEMENT) {
                        $afterArguments = $afterChild['value'];
                    } else {
                        $afterActions[] = $afterChild;
                    }
                }

                // Validate <actions>
                foreach ($beforeActions as $testAction) {
                    // Action group annotations, continue
                    if (!isset($testAction['attributes']['stepKey'])) {
                        continue;
                    }
                    $beforeFieldKey = $testAction['attributes']['stepKey'];
                    $matchingElement = $this->findMatchingElement(
                        $testAction,
                        $afterActions,
                        'stepKey'
                    );
                    if ($matchingElement === null) {
                        $operation = new ActionGroupActionRemove($filenames, $operationTarget . '/' . $beforeFieldKey);
                        $this->getReport()->add(MftfReport::MFTF_REPORT_CONTEXT, $operation);
                    } else {
                        $this->matchAndValidateAttributes(
                            $testAction['attributes'],
                            $matchingElement['attributes'],
                            $this->getReport(),
                            $filenames,
                            ActionGroupActionChanged::class,
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
                $this->findAddedElementsInArray(
                    $beforeActions,
                    $afterActions,
                    'stepKey',
                    $this->getReport(),
                    $filenames,
                    ActionGroupActionAdded::class,
                    $operationTarget
                );

                // Validate <arguments>
                foreach ($beforeArguments as $argument) {
                    $beforeFieldKey = $argument['attributes']['name'];
                    $matchingElement = $this->findMatchingElement($argument, $afterArguments,'name');
                    if ($matchingElement === null) {
                        $operation = new ActionGroupArgumentRemove(
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
                            ActionGroupArgumentChanged::class,
                            "$operationTarget/$beforeFieldKey"
                        );

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
        return $this->getReport();
    }
}
