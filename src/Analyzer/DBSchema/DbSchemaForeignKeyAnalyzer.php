<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Analyzer\DBSchema;

use Magento\SemanticVersionChecker\Analyzer\AnalyzerInterface;
use Magento\SemanticVersionChecker\Operation\ForeignKeyAdd;
use Magento\SemanticVersionChecker\Operation\ForeignKeyChange;
use Magento\SemanticVersionChecker\Operation\ForeignKeyDrop;
use PHPSemVerChecker\Registry\Registry;
use PHPSemVerChecker\Report\Report;

/**
 * Foreign key Analyzer
 */
class DbSchemaForeignKeyAnalyzer implements AnalyzerInterface
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
            $fileBefore = $registryBefore->mapping['table'][$moduleName];
            foreach ($moduleTables as $tableName => $tableData) {
                $keys = $tableData['foreign'] ?? [];
                foreach ($keys as $name => $key) {
                    if (!isset($registryTablesAfter[$moduleName][$tableName])) {
                        continue;
                    }
                    if ($key !== null && !isset($registryTablesAfter[$moduleName][$tableName]['foreign'][$name])) {
                        $operation = new ForeignKeyDrop($fileBefore, $tableName . '/' . $name);
                        $this->getReport()->add($this->context, $operation);
                        continue;
                    }
                    foreach ($key as $item => $value) {
                        if ($value !== $registryTablesAfter[$moduleName][$tableName]['foreign'][$name][$item]) {
                            $operation = new ForeignKeyChange($fileBefore, $tableName . '/' . $name . '/' . $item);
                            $this->getReport()->add($this->context, $operation);
                        }
                    }
                }
            }
        }

        foreach ($registryTablesAfter as $moduleName => $moduleTables) {
            $fileAfter = $registryAfter->mapping['table'][$moduleName];
            foreach ($moduleTables as $tableName => $tableData) {
                $keys = $tableData['foreign'] ?? [];
                foreach ($keys as $name => $key) {
                    if (!isset($registryTablesBefore[$moduleName][$tableName])) {
                        continue;
                    }
                    if ($key !== null && !isset($registryTablesBefore[$moduleName][$tableName]['foreign'][$name])) {
                        $operation = new ForeignKeyAdd($fileAfter, $tableName . '/' . $name);
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
