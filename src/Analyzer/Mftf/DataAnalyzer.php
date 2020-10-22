<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Analyzer\Mftf;

use Magento\SemanticVersionChecker\Analyzer\AnalyzerInterface;
use Magento\SemanticVersionChecker\MftfReport;
use Magento\SemanticVersionChecker\Operation\Mftf\Data\DataEntityAdded;
use Magento\SemanticVersionChecker\Operation\Mftf\Data\DataEntityArrayAdded;
use Magento\SemanticVersionChecker\Operation\Mftf\Data\DataEntityArrayRemoved;
use Magento\SemanticVersionChecker\Operation\Mftf\Data\DataEntityArrayItemRemoved;
use Magento\SemanticVersionChecker\Operation\Mftf\Data\DataEntityFieldAdded;
use Magento\SemanticVersionChecker\Operation\Mftf\Data\DataEntityFieldRemoved;
use Magento\SemanticVersionChecker\Operation\Mftf\Data\DataEntityRemoved;
use Magento\SemanticVersionChecker\Operation\Mftf\Data\DataEntityReqEntityAdded;
use Magento\SemanticVersionChecker\Operation\Mftf\Data\DataEntityReqEntityRemoved;
use Magento\SemanticVersionChecker\Operation\Mftf\Data\DataEntityVarAdded;
use Magento\SemanticVersionChecker\Operation\Mftf\Data\DataEntityVarRemoved;
use Magento\SemanticVersionChecker\Registry\XmlRegistry;
use Magento\SemanticVersionChecker\Scanner\MftfScanner;
use PHPSemVerChecker\Report\Report;

/**
 * Mftf Data entities analyzer class.
 */
class DataAnalyzer extends AbstractEntityAnalyzer implements AnalyzerInterface
{
    const MFTF_DATA_FIELD_ELEMENT = "{}data";
    const MFTF_VAR_ELEMENT = "{}var";
    const MFTF_REQ_ELEMENT = "{}requiredEntity";
    const MFTF_ARRAY_ELEMENT = "{}array";
    const MFTF_DATA_TYPE = 'entity';

    /**
     * MFTF data.xml analyzer
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
                    $operation = new DataEntityRemoved($filenames, $operationTarget);
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
                        $operation = new DataEntityFieldRemoved(
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
                        $operation = new DataEntityVarRemoved(
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
                        $operation = new DataEntityReqEntityRemoved(
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
                        $operation = new DataEntityArrayRemoved(
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
                            $operation = new DataEntityArrayItemRemoved(
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

        // check new modules
        $newModuleEntities = array_diff_key($afterEntities, $beforeEntities);
        foreach ($newModuleEntities as $module => $entities) {
            $this->findAddedEntitiesInModule(
                $beforeEntities[$module] ?? [],
                $entities,
                self::MFTF_DATA_TYPE,
                $this->getReport(),
                DataEntityAdded::class,
                $module . '/Data'
            );
        }
        return $this->getReport();
    }
}
