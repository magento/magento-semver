<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Analyzer\Mftf;

use Magento\SemanticVersionChecker\Analyzer\AnalyzerInterface;
use Magento\SemanticVersionChecker\MftfReport;
use Magento\SemanticVersionChecker\Operation\Mftf\Section\SectionAdded;
use Magento\SemanticVersionChecker\Operation\Mftf\Section\SectionElementAdded;
use Magento\SemanticVersionChecker\Operation\Mftf\Section\SectionElementChanged;
use Magento\SemanticVersionChecker\Operation\Mftf\Section\SectionElementParameterizedChanged;
use Magento\SemanticVersionChecker\Operation\Mftf\Section\SectionElementRemoved;
use Magento\SemanticVersionChecker\Operation\Mftf\Section\SectionElementSelectorChanged;
use Magento\SemanticVersionChecker\Operation\Mftf\Section\SectionElementTypeChanged;
use Magento\SemanticVersionChecker\Operation\Mftf\Section\SectionRemoved;
use Magento\SemanticVersionChecker\Registry\XmlRegistry;
use Magento\SemanticVersionChecker\Scanner\MftfScanner;
use PHPSemVerChecker\Report\Report;

/**
 * Mftf Section analyzer class.
 */
class SectionAnalyzer extends AbstractEntityAnalyzer implements AnalyzerInterface
{
    public const MFTF_ELEMENT_ELEMENT = "{}element";
    public const MFTF_DATA_TYPE = 'section';
    public const MFTF_ELEMENT_PARAM = 'parameterized';

    /**
     * operations array
     *
     * @var string[]
     */
    private static $operations = [
        AbstractEntityAnalyzer::DEFAULT_OPERATION_KEY => SectionElementChanged::class,
        'selector' => SectionElementSelectorChanged::class,
        'type' => SectionElementTypeChanged::class,
        self::MFTF_ELEMENT_PARAM => SectionElementParameterizedChanged::class
    ];

    /**
     * MFTF section.xml analyzer
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

                        // validate parameterized added
                        $beforeAttributes = $beforeField['attributes'];
                        $afterAttributes =  $matchingElement['attributes'];

                        if (isset($afterAttributes[self::MFTF_ELEMENT_PARAM])) {
                            if (!isset($beforeAttributes[self::MFTF_ELEMENT_PARAM])) {
                                $operation = new SectionElementParameterizedChanged(
                                    $filenames,
                                    "$operationTarget/$beforeFieldKey/" . self::MFTF_ELEMENT_PARAM
                                );
                                $this->getReport()->add(MftfReport::MFTF_REPORT_CONTEXT, $operation);
                            }
                        }
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

        // check new modules
        $newModuleEntities = array_diff_key($afterEntities, $beforeEntities);
        foreach ($newModuleEntities as $module => $entities) {
            $this->findAddedEntitiesInModule(
                $beforeEntities[$module] ?? [],
                $entities,
                self::MFTF_DATA_TYPE,
                $this->getReport(),
                SectionAdded::class,
                $module . '/Section'
            );
        }
        return $this->getReport();
    }
}
