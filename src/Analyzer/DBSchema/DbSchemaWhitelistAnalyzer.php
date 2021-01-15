<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Analyzer\DBSchema;

use Magento\SemanticVersionChecker\Analyzer\AnalyzerInterface;
use Magento\SemanticVersionChecker\Operation\InvalidWhitelist;
use Magento\SemanticVersionChecker\Operation\WhiteListWasRemoved;
use PHPSemVerChecker\Registry\Registry;
use PHPSemVerChecker\Report\Report;

/**
 * Implements an analyzer fdr the database schema whitelist files.
 * @noinspection PhpUnused
 */
class DbSchemaWhitelistAnalyzer implements AnalyzerInterface
{
    /**
     * Analyzer context.
     *
     * @var string
     */
    protected $context = 'db_schema';
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
     * Class analyzer.
     *
     * @param Registry $registryBefore
     * @param Registry $registryAfter
     *
     * @return Report
     */
    public function analyze($registryBefore, $registryAfter)
    {
        $registryTablesAfter = $registryAfter->data['table'] ?? [];
        $dbWhiteListContent = $registryAfter->data['whitelist_json'] ?? [];

        foreach ($registryTablesAfter as $moduleName => $tablesData) {
            $whiteListFileAfter = $registryAfter->mapping['whitelist_json'][$moduleName] ?? '';
            if (!file_exists($whiteListFileAfter)) {
                $operation = new WhiteListWasRemoved($whiteListFileAfter, $moduleName);
                $this->report->add('database', $operation);
                continue;
            }
            if (count($tablesData)) {
                foreach (array_keys($tablesData) as $table) {
                    if (!isset($dbWhiteListContent[$moduleName][$table])) {
                        $operation = new InvalidWhitelist($whiteListFileAfter, $table);
                        $this->report->add('database', $operation);
                    }
                }
            }
        }
        return $this->report;
    }
}
