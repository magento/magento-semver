<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tools\SemanticVersionChecker\Analyzer;

use Magento\Tools\SemanticVersionChecker\Operation\ColumnRemove;
use Magento\Tools\SemanticVersionChecker\Operation\DropForeignKey;
use Magento\Tools\SemanticVersionChecker\Operation\DropKey;
use Magento\Tools\SemanticVersionChecker\Operation\TableChangeResource;
use Magento\Tools\SemanticVersionChecker\Operation\TableDropped;
use PHPSemVerChecker\Registry\Registry;
use PHPSemVerChecker\Report\Report;

/**
 * @inheritDoc
 */
class DbSchemaAnalyzer implements AnalyzerInterface
{
    /**
     * Analyzer context.
     *
     * @var string
     */
    protected $context = 'db_schema';

    /**
     * @var Report|null
     */
    private $report = null;

    /**
     * Class analyzer.
     *
     * @param Registry $registryBefore
     * @param Registry $registryAfter
     * @return Report
     */
    public function analyze($registryBefore, $registryAfter)
    {
        $this->report = new Report();
        $registryTablesBefore = $registryBefore->data['table'] ?? [];
        $registryTablesAfter = $registryAfter->data['table'] ?? [];

        foreach ($registryTablesBefore as $moduleName => $moduleTables) {
            foreach ($moduleTables as $tableName => $tableData) {
                if (!isset($registryTablesAfter[$moduleName][$tableName])) {
                    $operation = new TableDropped($moduleName, $tableName);
                    $this->getReport()->add('database', $operation);
                } else {
                    $this->validateDatabaseChanges(
                        $tableData,
                        $registryTablesAfter,
                        $moduleName,
                        $tableName
                    );
                }
            }
        }

        return $this->getReport();
    }

    /**
     * Get report.
     *
     * @return Report
     */
    private function getReport(): Report
    {
        return $this->report;
    }

    /**
     * Validate database changes.
     *
     * @param array $tableData
     * @param array $registryTablesAfter
     * @param string $moduleName
     * @param string $tableName
     */
    private function validateDatabaseChanges(
        array $tableData,
        array $registryTablesAfter,
        string $moduleName,
        string $tableName
    ) {
        $columns = $tableData['column'] ?? [];
        $keys = $tableData['key'] ?? [];
        $fks = $tableData['foreign'] ?? [];

        if ($tableData['resource'] !== $registryTablesAfter[$moduleName][$tableName]['resource']) {
            $operation = new TableChangeResource(
                $moduleName,
                $tableName,
                $tableData['resource'],
                $registryTablesAfter[$moduleName][$tableName]['resource']
            );
            $this->getReport()->add('database', $operation);
        }

        //Process columns
        foreach ($columns as $column) {
            if (!isset($registryTablesAfter[$moduleName][$tableName]['column'][$column])) {
                $operation = new ColumnRemove($moduleName, $tableName . '/' . $column);
                $this->getReport()->add('database', $operation);
            }
        }
        //Process keys
        foreach ($keys as $key) {
            if ($key !== null && !isset($registryTablesAfter[$moduleName][$tableName]['key'][$key])) {
                $operation = new DropKey($moduleName, $tableName . '/' . $key);
                $this->getReport()->add('database', $operation);
            }
        }
        //Process foreign keys
        foreach ($fks as $key) {
            if ($key !== null && !isset($registryTablesAfter[$moduleName][$tableName]['foreign'][$key])) {
                $operation = new DropForeignKey($moduleName, $tableName . '/' . $key);
                $this->getReport()->add('database', $operation);
            }
        }
    }
}
