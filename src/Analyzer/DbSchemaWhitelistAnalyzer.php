<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Analyzer;

use Magento\SemanticVersionChecker\Operation\InvalidWhitelist;
use Magento\SemanticVersionChecker\Operation\WhiteListWasRemoved;
use PHPSemVerChecker\Registry\Registry;
use PHPSemVerChecker\Report\Report;

/**
 * Class DbSchemaAnalyzer
 * @package Magento\SemanticVersionChecker\Analyzer
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
     * Class analyzer.
     *
     * @param Registry $registryBefore
     * @param Registry $registryAfter
     * @return Report
     */
    public function analyze($registryBefore, $registryAfter)
    {
        $report = new Report();
        $registryTablesAfter = $registryAfter->data['table'] ?? [];
        $registryTablesBefore = $registryBefore->data['table'] ?? [];

        foreach ($registryTablesAfter as $moduleName => $tablesData) {
            if (count($tablesData)) {
                //Take file like an example
                //We will replace module_name in file_path in order to get
                //correct module
                $dbFile = $registryAfter->getCurrentFile();
                $dbWhiteListFile = preg_replace('/(.*Magento\/)\w+(\/.*)/', '$1' . explode("_", $moduleName)[1] . '$2', $dbFile);
                $dbWhiteListFile = str_replace('db_schema.xml', 'db_schema_whitelist.json', $dbWhiteListFile);
                if (!file_exists($dbWhiteListFile)) {
                    $operation = new WhiteListWasRemoved($moduleName);
                    $report->add('database', $operation);
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
                        $report->add('database', $operation);
                    }
                }
            }
        }


        return $report;
    }
}
