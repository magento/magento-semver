<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Analyzer\DBSchema;

use Magento\SemanticVersionChecker\Analyzer\AnalyzerInterface;
use Magento\SemanticVersionChecker\Operation\ColumnAdd;
use Magento\SemanticVersionChecker\Operation\ColumnRemove;
use PHPSemVerChecker\Registry\Registry;
use PHPSemVerChecker\Report\Report;

/**
 * Analyzes columns in db_schema.xml
 */
class DbSchemaColumnAnalyzer implements AnalyzerInterface
{
    /**
     * Analyzer context.
     *
     * @var string
     */
    private $context = 'database';

    /**
     * @var Report|null
     */
    private $report = null;

    /**
     * DbSchemaColumnAnalyzer constructor
     *
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
                $columns = $tableData['column'] ?? [];
                foreach ($columns as $column) {
                    if (
                        isset($registryTablesAfter[$moduleName][$tableName])
                        && !isset($registryTablesAfter[$moduleName][$tableName]['column'][$column])
                    ) {
                        $operation = new ColumnRemove($moduleName, $tableName . '/' . $column);
                        $this->getReport()->add($this->context, $operation);
                    }
                }
            }
        }
        foreach ($registryTablesAfter as $moduleName => $moduleTables) {
            foreach ($moduleTables as $tableName => $tableData) {
                $columns = $tableData['column'] ?? [];
                foreach ($columns as $column) {
                    if (
                        isset($registryTablesBefore[$moduleName][$tableName])
                        && !isset($registryTablesBefore[$moduleName][$tableName]['column'][$column])
                    ) {
                        $operation = new ColumnAdd($moduleName, $tableName . '/' . $column);
                        $this->getReport()->add($this->context, $operation);
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
