<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Analyzer\Mftf;

use Magento\SemanticVersionChecker\Analyzer\AnalyzerInterface;
use Magento\SemanticVersionChecker\MftfReport;
use Magento\SemanticVersionChecker\Operation\Mftf\Metadata\MetadataAdded;
use Magento\SemanticVersionChecker\Operation\Mftf\Metadata\MetadataChanged;
use Magento\SemanticVersionChecker\Operation\Mftf\Metadata\MetadataChildAdded;
use Magento\SemanticVersionChecker\Operation\Mftf\Metadata\MetadataChildRemoved;
use Magento\SemanticVersionChecker\Operation\Mftf\Metadata\MetadataRemoved;
use Magento\SemanticVersionChecker\Registry\XmlRegistry;
use Magento\SemanticVersionChecker\Scanner\MftfScanner;
use PHPSemVerChecker\Report\Report;

/**
 * Mftf MetaData analyzer class.
 */
class MetadataAnalyzer extends AbstractEntityAnalyzer implements AnalyzerInterface
{
    const MFTF_DATA_TYPE = 'operation';

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
                MetadataAdded::class,
                $module . '/ActionGroup'
            );
            foreach ($entities as $entityName => $beforeEntity) {
                if ($beforeEntity['type'] !== self::MFTF_DATA_TYPE) {
                    continue;
                }
                $operationTarget = $module . '/Metadata/' . $entityName;
                $filenames = implode(", ", $beforeEntity['filePaths']);

                // Validate section still exists
                if (!isset($afterEntities[$module][$entityName])) {
                    $operation = new MetadataRemoved($filenames, $operationTarget);
                    $this->getReport()->add(MftfReport::MFTF_REPORT_CONTEXT, $operation);
                    continue;
                }

               // Validate metadata attribute changes
               $this->matchAndValidateAttributes(
                   $beforeEntity['attributes'],
                   $afterEntities[$module][$entityName]['attributes'],
                   $this->getReport(),
                   $filenames,
                   [AbstractEntityAnalyzer::DEFAULT_OPERATION_KEY => MetadataChanged::class],
                   $operationTarget
               );

                // Validate child elements removed
                $this->recursiveCompare(
                    $beforeEntity,
                    $afterEntities[$module][$entityName],
                    MetadataChildRemoved::class,
                    $operationTarget,
                    $filenames,
                    $this->getReport()
                );

                // Validate child elements added
                $this->recursiveCompare(
                    $afterEntities[$module][$entityName],
                    $beforeEntity,
                    MetadataChildAdded::class,
                    $operationTarget,
                    $filenames,
                    $this->getReport()
                );
            }
        }
        return $this->getReport();
    }

    /**
     * Compares child xml elements of entity for parity, as well as child of child elements
     *
     * @param array $beforeEntity
     * @param array $afterEntity
     * @param string $operationClass
     * @param string $operationTarget
     * @param string $filenames
     * @param Report $report
     * @return void
     */
    public function recursiveCompare($beforeEntity, $afterEntity, $operationClass, $operationTarget, $filenames, $report)
    {
        $beforeChildren = $beforeEntity['value'] ?? [];
        $afterChildren = $afterEntity['value'] ?? [];
        if (!is_array($beforeChildren)) {
            return;
        }
        foreach ($beforeChildren as $beforeChild) {
            $beforeType = $beforeChild['name'];
            $beforeFieldKey = $beforeChild['attributes']['key'] ?? null;
            $afterFound = null;
            foreach ($afterChildren as $afterChild) {
                if ($afterChild['name'] !== $beforeType) {
                    continue;
                }
                $afterFieldKey = $afterChild['attributes']['key'] ?? null;
                if ($afterFieldKey === $beforeFieldKey) {
                    $afterFound = $afterChild;
                    break;
                }
            }
            if ($afterFound === null) {
                $operation = new $operationClass($filenames, $operationTarget . '/' . $beforeFieldKey);
                $report->add(MftfReport::MFTF_REPORT_CONTEXT, $operation);
            } else {
                $this->recursiveCompare(
                    $beforeChild,
                    $afterFound,
                    $operationClass,
                    $operationTarget . '/' . $beforeFieldKey,
                    $filenames,
                    $report
                );
            }
        }
    }
}
