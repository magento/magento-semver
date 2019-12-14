<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Analyzer\DBSchema;

use Magento\SemanticVersionChecker\Analyzer\AnalyzerInterface;
use Magento\SemanticVersionChecker\Operation\UniqueKeyAdd;
use Magento\SemanticVersionChecker\Operation\UniqueKeyChange;
use Magento\SemanticVersionChecker\Operation\UniqueKeyDrop;
use PHPSemVerChecker\Registry\Registry;
use PHPSemVerChecker\Report\Report;

/**
 * @inheritDoc
 */
class DbSchemaUniqueKeyAnalyzer implements AnalyzerInterface
{
    /**
     * Analyzer context
     *
     * @var string
     */
    private $context = 'database';

    /**
     * @var Report|null
     */
    private $report = null;

    /**
     * @param Report $report
     */
    public function __construct(
        Report $report
    ) {
        $this->report = $report;
    }

    /**
     * Class analyzer
     *
     * @param Registry $registryBefore
     * @param Registry $registryAfter
     *
     * @return Report
     */
    public function analyze($registryBefore, $registryAfter)
    {
        $registryTablesBefore = $registryBefore->data['table'] ?? [];
        $registryTablesAfter = $registryAfter->data['table'] ?? [];

        foreach ($registryTablesBefore as $moduleName => $moduleTables) {
            foreach ($moduleTables as $tableName => $tableData) {
                $keys = $tableData['unique'] ?? [];
                foreach ($keys as $name => $key) {
                    if (!isset($registryTablesAfter[$moduleName][$tableName])) {
                        continue;
                    }
                    if ($key !== null && !isset($registryTablesAfter[$moduleName][$tableName]['unique'][$name])) {
                        $operation = new UniqueKeyDrop($moduleName, $tableName . '/' . $name);
                        $this->getReport()->add($this->context, $operation);
                        continue;
                    }
                    foreach ($key['columns'] as $beforeIndex => $beforeColumn) {
                        $matchedColumnFlag = false;
                        $columns = $registryTablesAfter[$moduleName][$tableName]['unique'][$name]['columns'];
                        foreach ($columns as $afterIndex => $afterColumn) {
                            if ($beforeColumn === $afterColumn) {
                                $matchedColumnFlag = true;
                                break;
                            }
                        }
                        if (!$matchedColumnFlag) {
                            $operation = new UniqueKeyChange($moduleName, $tableName . '/' . $name);
                            $this->getReport()->add($this->context, $operation);
                            break;
                        }
                    }
                }
            }
        }

        foreach ($registryTablesAfter as $moduleName => $moduleTables) {
            foreach ($moduleTables as $tableName => $tableData) {
                $keys = $tableData['unique'] ?? [];
                foreach ($keys as $name => $key) {
                    if (!isset($registryTablesBefore[$moduleName][$tableName])) {
                        continue;
                    }
                    if ($key !== null && !isset($registryTablesBefore[$moduleName][$tableName]['unique'][$name])) {
                        $operation = new UniqueKeyAdd($moduleName, $tableName . '/' . $name);
                        $this->getReport()->add($this->context, $operation);
                        continue;
                    }

                    foreach ($key['columns'] as $beforeIndex => $beforeColumn) {
                        $matchedColumnFlag = false;
                        $columns = $registryTablesBefore[$moduleName][$tableName]['unique'][$name]['columns'];
                        foreach ($columns as $afterIndex => $afterColumn) {
                            if ($beforeColumn === $afterColumn) {
                                $matchedColumnFlag = true;
                                break;
                            }
                        }
                        if (!$matchedColumnFlag) {
                            $operation = new UniqueKeyChange($moduleName, $tableName . '/' . $name);
                            $this->getReport()->add($this->context, $operation);
                            break;
                        }
                    }
                }
            }
        }

        return $this->getReport();
    }

    /**
     * Get report
     *
     * @return Report
     */
    private function getReport(): Report
    {
        return $this->report;
    }
}
