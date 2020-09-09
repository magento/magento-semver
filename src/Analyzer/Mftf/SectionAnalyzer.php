<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Analyzer\Mftf;

use Magento\SemanticVersionChecker\MftfReport;
use Magento\SemanticVersionChecker\Operation\Mftf\Section\SectionAdded;
use Magento\SemanticVersionChecker\Operation\Mftf\Section\SectionElementAdded;
use Magento\SemanticVersionChecker\Operation\Mftf\Section\SectionElementChanged;
use Magento\SemanticVersionChecker\Operation\Mftf\Section\SectionElementParameterizedChanged;
use Magento\SemanticVersionChecker\Operation\Mftf\Section\SectionElementRemoved;
use Magento\SemanticVersionChecker\Operation\Mftf\Section\SectionElementSelectorChanged;
use Magento\SemanticVersionChecker\Operation\Mftf\Section\SectionElementTypeChanged;
use Magento\SemanticVersionChecker\Operation\Mftf\Section\SectionRemoved;
use Magento\SemanticVersionChecker\Scanner\MftfScanner;
use PHPSemVerChecker\Registry\Registry;
use PHPSemVerChecker\Report\Report;

class SectionAnalyzer extends AbstractEntityAnalyzer
{
    const MFTF_ELEMENT_ELEMENT = "{}element";
    const MFTF_DATA_TYPE = 'section';
    const MFTF_DATA_DIRECTORY = '/Mftf/Section/';

    /**
     * operations array
     *
     * @var string[]
     */
    private static $operations = [
        AbstractEntityAnalyzer::DEFAULT_OPERATION_KEY => SectionElementChanged::class,
        'selector' => SectionElementSelectorChanged::class,
        'type' => SectionElementTypeChanged::class,
        'parameterized' => SectionElementParameterizedChanged::class
    ];

    /**
     * MFTF section.xml analyzer
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
                SectionAdded::class,
                $module . '/Section'
            );
            foreach ($entities as $entityName => $beforeEntity) {
                if ($beforeEntity['type'] !== self::MFTF_DATA_TYPE) {
                    continue;
                }
                $operationTarget = $module . '/Section/' . $entityName;
                $filenames = implode(", ", $beforeEntity['filePaths']);

                // Validate section still exists
                if (!isset($afterEntities[$module][$entityName])) {
                    $operation = new SectionRemoved($filenames, $operationTarget);
                    $this->getReport()->add(MftfReport::MFTF_REPORT_CONTEXT, $operation);
                    continue;
                }
                // Sort Elements
                $beforeElements = [];
                $afterElements = [];

                foreach ($beforeEntity['value'] ?? [] as $beforeChild) {
                    if ($beforeChild['name'] == self::MFTF_ELEMENT_ELEMENT) {
                        $beforeElements[] = $beforeChild;
                    }
                }
                foreach ($afterEntities[$module][$entityName]['value'] ?? [] as $afterChild) {
                    if ($afterChild['name'] == self::MFTF_ELEMENT_ELEMENT) {
                        $afterElements[] = $afterChild;
                    }
                }

                // Validate <element> elements
                foreach ($beforeElements as $beforeField) {
                    $beforeFieldKey = $beforeField['attributes']['name'];
                    $matchingElement = $this->findMatchingElement(
                        $beforeField,
                        $afterElements,
                        'name'
                    );
                    if ($matchingElement === null) {
                        $operation = new SectionElementRemoved(
                            $filenames,
                            $operationTarget . '/' . $beforeFieldKey
                        );
                        $this->getReport()->add(MftfReport::MFTF_REPORT_CONTEXT, $operation);
                    } else {
                        $this->matchAndValidateAttributes(
                            $beforeField['attributes'],
                            $matchingElement['attributes'],
                            $this->getReport(),
                            $filenames,
                            self::$operations,
                            "$operationTarget/$beforeFieldKey"
                        );
                    }
                }
                $this->findAddedElementsInArray(
                    $beforeElements,
                    $afterElements,
                    'name',
                    $this->getReport(),
                    $filenames,
                    SectionElementAdded::class,
                    $operationTarget
                );
            }
        }
        return $this->getReport();
    }
}
