<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Analyzer\DBSchema;

use Magento\SemanticVersionChecker\Analyzer\AnalyzerInterface;
use Magento\SemanticVersionChecker\Operation\InvalidWhitelist;
use Magento\SemanticVersionChecker\Operation\WhiteListWasRemoved;
use PHPSemVerChecker\Registry\Registry;
use PHPSemVerChecker\Report\Report;

/**
 * Implements an analyzer for the database schema whitelist files
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
     * DbSchemaWhitelistAnalyzer constructor.
     * @param Report $report
     */
    public function __construct(Report $report) {
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
        $registryTablesAfter = $registryAfter->data['table'] ?? [];
        $registryTablesBefore = $registryBefore->data['table'] ?? [];

        //Take file like an example
        //We will replace module_name in file_path in order to get
        //correct module
        $dbFile = $registryAfter->getCurrentFile();
        foreach ($registryTablesAfter as $moduleName => $tablesData) {
            if (count($tablesData)) {
                $dbWhiteListFile =
                $dbWhiteListFile = preg_replace(
                    '/(.*Magento\/)\w+(\/.*)(db_schema\.xml)/',
                    '$1' . explode("_", $moduleName)[1] . '$2'
                    . 'db_schema_whitelist.json',
                    $dbFile
                );
                if (!file_exists($dbWhiteListFile)) {
                    $operation = new WhiteListWasRemoved($dbWhiteListFile, $moduleName);
                    $this->report->add('database', $operation);
                    continue;
                } else {
                    $dbWhiteListContent = json_decode(
                        file_get_contents($dbWhiteListFile),
                        true
                    );
                }

                $tables = array_replace($tablesData, $registryTablesBefore[$moduleName] ?? []);
                foreach (array_keys($tables) as $table) {
                    if (!isset($dbWhiteListContent[$table])) {
                        $operation = new InvalidWhitelist($dbWhiteListFile, $table);
                        $this->report->add('database', $operation);
                    }
                }
            }
        }
        return $this->report;
    }
}
