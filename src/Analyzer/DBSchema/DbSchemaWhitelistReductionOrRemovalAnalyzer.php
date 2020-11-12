<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Analyzer\DBSchema;

use Magento\SemanticVersionChecker\Analyzer\AnalyzerInterface;
use Magento\SemanticVersionChecker\Operation\WhiteListReduced;
use Magento\SemanticVersionChecker\Operation\WhiteListWasRemoved;
use PHPSemVerChecker\Registry\Registry;
use PHPSemVerChecker\Report\Report;

/**
 * Whitelist elements removal analyzer
 */
class DbSchemaWhitelistReductionOrRemovalAnalyzer implements AnalyzerInterface
{
    /**
     * @var Report
     */
    private $report;

    /**
     * @param Report $report
     */
    public function __construct(
        Report $report
    ) {
        $this->report = $report;
    }

    /**
     * Analyze content difference for schema whitelist.
     *
     * @param Registry $registryBefore
     * @param Registry $registryAfter
     *
     * @return Report
     */
    public function analyze($registryBefore, $registryAfter)
    {
        $whiteListBefore = $registryBefore->data['whitelist_json'] ?? [];
        $whiteListAfter = $registryAfter->data['whitelist_json'] ?? [];

        /** @var array $tablesData */
        foreach ($whiteListBefore as $moduleName => $beforeModuleTablesData) {
            $fileBefore = $registryBefore->mapping['whitelist_json'][$moduleName];
            if (!isset($whiteListAfter[$moduleName])) {
                $operation = new WhiteListWasRemoved($fileBefore, $moduleName);
                $this->report->add('database', $operation);
                continue;
            }
            $afterModuleTablesData = $whiteListAfter[$moduleName];
            /** @var array $beforeTableData */
            foreach ($beforeModuleTablesData as $tableName => $beforeTableData) {
                if (!$this->isArrayExistsAndHasSameSize($afterModuleTablesData, $beforeTableData, $tableName)) {
                    $this->addReport($fileBefore, $tableName);
                    continue;
                }
                $afterTableData = $afterModuleTablesData[$tableName];
                /**  @var array $beforeTablePartData */
                foreach ($beforeTableData as $tablePartName => $beforeTablePartData) {
                    if (!$this->isArrayExistsAndHasSameSize($afterTableData, $beforeTablePartData, $tablePartName)) {
                        $this->addReport($fileBefore, $tableName .  '/' . $tablePartName);
                        continue;
                    }
                    $afterTablePartData = $afterTableData[$tablePartName];
                    /**  @var bool $beforeStatus */
                    foreach ($beforeTablePartData as $name => $beforeStatus) {
                        //checks if array exists in new whitelist.json and if it has different amount of items inside
                        if (!isset($afterTablePartData[$name])) {
                            $this->addReport($fileBefore, $tableName .  '/' . $tablePartName . '/' . $name);
                        }
                    }
                }
            }
        }

        return $this->report;
    }

    /**
     * Checks if array exists in new whitelist.json and if it has different amount of items inside
     *
     * @param array $after
     * @param array $beforeArray
     * @param string $name
     *
     * @return bool
     */
    public function isArrayExistsAndHasSameSize(array $after, array $beforeArray, string $name): bool
    {
        if (isset($after[$name])) {
            return count($beforeArray) <= count($after[$name]);
        }

        return false;
    }

    /**
     * @param string $filePath
     * @param string $target
     *
     * @return void
     */
    public function addReport(string $filePath, string $target): void
    {
        $operation = new WhiteListReduced($filePath, $target);
        $this->report->add('database', $operation);
    }
}
