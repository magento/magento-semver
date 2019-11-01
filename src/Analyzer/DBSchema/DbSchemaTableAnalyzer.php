<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Analyzer\DBSchema;

use Magento\SemanticVersionChecker\Analyzer\AnalyzerInterface;
use Magento\SemanticVersionChecker\Operation\TableAdded;
use Magento\SemanticVersionChecker\Operation\TableChangeResource;
use Magento\SemanticVersionChecker\Operation\TableDropped;
use PHPSemVerChecker\Registry\Registry;
use PHPSemVerChecker\Report\Report;

/**
 * @inheritDoc
 */
class DbSchemaTableAnalyzer implements AnalyzerInterface
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
     * Class analyzer.
     *
     * @param Registry $registryBefore
     * @param Registry $registryAfter
     * @return Report
     */
    public function analyze($registryBefore, $registryAfter)
    {
        $registryTablesBefore = $registryBefore->data['table'] ?? [];
        $registryTablesAfter = $registryAfter->data['table'] ?? [];

        foreach ($registryTablesBefore as $moduleName => $moduleTables) {
            foreach ($moduleTables as $tableName => $tableData) {
                if (!isset($registryTablesAfter[$moduleName][$tableName])) {
                    $operation = new TableDropped($moduleName, $tableName);
                    $this->getReport()->add($this->context, $operation);
                    continue;
                }
                if ($tableData['resource'] !== $registryTablesAfter[$moduleName][$tableName]['resource']) {
                    $operation = new TableChangeResource(
                        $moduleName,
                        $tableName,
                        $tableData['resource'],
                        $registryTablesAfter[$moduleName][$tableName]['resource']
                    );
                    $this->getReport()->add($this->context, $operation);
                }
            }
        }
        foreach ($registryTablesAfter as $moduleName => $moduleTables) {
            foreach ($moduleTables as $tableName => $tableData) {
                if (!isset($registryTablesBefore[$moduleName][$tableName])
                    && !$this->isModificationTableDeclaration($registryTablesAfter, $moduleName, $tableName)
                ) {
                    $operation = new TableAdded($moduleName, $tableName);
                    $this->getReport()->add($this->context, $operation);
                }
            }
        }

        return $this->getReport();
    }

    /**
     * Checks if table was declared just one time
     *
     * @param array $registryTablesAfter
     * @param string $originalModuleName
     * @param string $originalTableName
     * @return bool
     */
    private function isModificationTableDeclaration(
        array $registryTablesAfter,
        string $originalModuleName,
        string $originalTableName
    ): bool {
        foreach ($registryTablesAfter as $moduleName => $moduleTables) {
            if ($originalModuleName !== $moduleName) {
                foreach ($moduleTables as $tableName => $tableData) {
                    if ($tableName === $originalTableName) {
                        return true;
                    }
                }
            }
        }

        return false;
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
}
