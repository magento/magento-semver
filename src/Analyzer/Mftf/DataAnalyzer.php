<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Analyzer\Mftf;

use Magento\SemanticVersionChecker\MftfReport;
use Magento\SemanticVersionChecker\Operation\Mftf\Data\DataEntityAdded;
use Magento\SemanticVersionChecker\Operation\Mftf\Data\DataEntityArrayAdded;
use Magento\SemanticVersionChecker\Operation\Mftf\Data\DataEntityArrayRemove;
use Magento\SemanticVersionChecker\Operation\Mftf\Data\DataEntityArrayItemRemove;
use Magento\SemanticVersionChecker\Operation\Mftf\Data\DataEntityFieldAdded;
use Magento\SemanticVersionChecker\Operation\Mftf\Data\DataEntityFieldRemove;
use Magento\SemanticVersionChecker\Operation\Mftf\Data\DataEntityRemove;
use Magento\SemanticVersionChecker\Operation\Mftf\Data\DataEntityReqEntityAdded;
use Magento\SemanticVersionChecker\Operation\Mftf\Data\DataEntityReqEntityRemove;
use Magento\SemanticVersionChecker\Operation\Mftf\Data\DataEntityVarAdded;
use Magento\SemanticVersionChecker\Operation\Mftf\Data\DataEntityVarRemove;
use Magento\SemanticVersionChecker\Scanner\MftfScanner;
use PHPSemVerChecker\Registry\Registry;
use PHPSemVerChecker\Report\Report;

class DataAnalyzer extends AbstractEntityAnalyzer
{
    const MFTF_DATA_FIELD_ELEMENT = "{}data";
    const MFTF_VAR_ELEMENT = "{}var";
    const MFTF_REQ_ELEMENT = "{}requiredEntity";
    const MFTF_ARRAY_ELEMENT = "{}array";
    const MFTF_DATA_TYPE = 'entity';
    const MFTF_DATA_DIRECTORY = '/Mftf/Data/';

    /**
     * MFTF data.xml analyzer
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
                DataEntityAdded::class,
                $module . '/Data'
            );
            foreach ($entities as $entityName => $beforeEntity) {
                if ($beforeEntity['type'] !== self::MFTF_DATA_TYPE) {
                    continue;
                }
                $operationTarget = $module . '/Data/' . $entityName;
                $filenames = implode(", ", $beforeEntity['filePaths']);

                // Validate data entity still exists
                if (!isset($afterEntities[$module][$entityName])) {
                    $operation = new DataEntityRemove($filenames, $operationTarget);
                    $this->getReport()->add(MftfReport::MFTF_REPORT_CONTEXT, $operation);
                    continue;
                }                

                // Sort Elements
                $beforeDataFields = [];
                $beforeVarFields = [];
                $beforeReqFields = [];
                $beforeArrayFields = [];

                $afterDataFields = [];
                $afterVarFields = [];
                $afterReqFields = [];
                $afterArrayFields = [];

                foreach ($beforeEntity['value'] ?? [] as $beforeChild) {
                    if ($beforeChild['name'] == self::MFTF_DATA_FIELD_ELEMENT) {
                        $beforeDataFields[] = $beforeChild;
                    } elseif ($beforeChild['name'] == self::MFTF_VAR_ELEMENT) {
                        $beforeVarFields[] = $beforeChild;
                    } elseif ($beforeChild['name'] == self::MFTF_REQ_ELEMENT) {
                        $beforeReqFields[] = $beforeChild;
                    } elseif ($beforeChild['name'] == self::MFTF_ARRAY_ELEMENT) {
                        $beforeArrayFields[] = $beforeChild;
                    }
                }

                foreach ($afterEntities[$module][$entityName]['value'] ?? [] as $afterChild) {
                    if ($afterChild['name'] == self::MFTF_DATA_FIELD_ELEMENT) {
                        $afterDataFields[] = $afterChild;
                    } elseif ($afterChild['name'] == self::MFTF_VAR_ELEMENT) {
                        $afterVarFields[] = $afterChild;
                    } elseif ($afterChild['name'] == self::MFTF_REQ_ELEMENT) {
                        $afterReqFields[] = $afterChild;
                    } elseif ($afterChild['name'] == self::MFTF_ARRAY_ELEMENT) {
                        $afterArrayFields[] = $afterChild;
                    }
                }
                
                // Validate <data> fields
                foreach ($beforeDataFields as $beforeField) {
                    $beforeFieldKey = $beforeField['attributes']['key'];
                    $matchingElement = $this->findMatchingElement(
                        $beforeField,
                        $afterDataFields,
                        'key'
                    );
                    if ($matchingElement === null) {
                        $operation = new DataEntityFieldRemove(
                            $filenames,
                            $operationTarget . '/' . $beforeFieldKey
                        );
                        $this->getReport()->add(MftfReport::MFTF_REPORT_CONTEXT, $operation);
                    }
                }
                $this->findAddedElementsInArray(
                    $beforeDataFields,
                    $afterDataFields,
                    'key',
                    $this->getReport(),
                    $filenames,
                    DataEntityFieldAdded::class,
                    $operationTarget
                );
                // Validate <var> fields
                foreach ($beforeVarFields as $beforeField) {
                    $beforeFieldKey = $beforeField['attributes']['key'];
                    $matchingElement = $this->findMatchingElement($beforeField, $afterVarFields,'key');
                    if ($matchingElement === null) {
                        $operation = new DataEntityVarRemove(
                            $filenames,
                            $operationTarget . '/' . $beforeFieldKey
                        );
                        $this->getReport()->add(MftfReport::MFTF_REPORT_CONTEXT, $operation);
                    }
                }
                $this->findAddedElementsInArray(
                    $beforeVarFields,
                    $afterVarFields,
                    'key',
                    $this->getReport(),
                    $filenames,
                    DataEntityVarAdded::class,
                    $operationTarget
                );
                // Validate <required-entity> fields
                foreach ($beforeReqFields as $beforeField) {
                    $beforeFieldValue = $beforeField['value'];
                    $matchingElement = $this->findMatchingElementByKeyAndValue(
                        $beforeField,
                        $afterReqFields,
                        'type'
                    );
                    if ($matchingElement === null) {
                        $operation = new DataEntityReqEntityRemove(
                            $filenames,
                            $operationTarget . '/' . $beforeFieldValue
                        );
                        $this->getReport()->add(MftfReport::MFTF_REPORT_CONTEXT, $operation);
                    }
                }
                $this->findAddedElementsInArrayByValue(
                    $beforeReqFields,
                    $afterReqFields,
                    'type',
                    $this->getReport(),
                    $filenames,
                    DataEntityReqEntityAdded::class,
                    $operationTarget
                );
                // Validate <array> fields
                foreach ($beforeArrayFields as $beforeField) {
                    $beforeFieldKey = $beforeField['attributes']['key'];
                    $matchingElement = $this->findMatchingElement(
                        $beforeField,
                        $afterArrayFields,
                        'key'
                    );
                    if ($matchingElement === null) {
                        $operation = new DataEntityArrayRemove(
                            $filenames,
                            $operationTarget . '/' . $beforeFieldKey
                        );
                        $this->getReport()->add(MftfReport::MFTF_REPORT_CONTEXT, $operation);
                    } else {
                        $itemValues = [];
                        foreach ($beforeField['value'] as $arrayItemNode) {
                            $itemValues[] = $arrayItemNode['value'];
                        }
                        foreach ($matchingElement['value'] as $afterArrayItemNode) {
                            if (($key = array_search($afterArrayItemNode['value'], $itemValues)) !== false) {
                                unset($itemValues[$key]);
                            }
                        }
                        if (count($itemValues) !== 0) {
                            $operation = new DataEntityArrayItemRemove(
                                $filenames,
                                $operationTarget . '/' . $beforeFieldKey . '/(' . implode(", ", $itemValues) . ")"
                            );
                            $this->getReport()->add(MftfReport::MFTF_REPORT_CONTEXT, $operation);
                        }
                    }
                }
                $this->findAddedElementsInArray(
                    $beforeArrayFields,
                    $afterArrayFields,
                    'key',
                    $this->getReport(),
                    $filenames,
                    DataEntityArrayAdded::class,
                    $operationTarget
                );
            }
        }
        return $this->getReport();
    }
}
