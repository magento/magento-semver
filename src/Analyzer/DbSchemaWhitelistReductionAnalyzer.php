<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Analyzer;

use Magento\SemanticVersionChecker\Operation\WhiteListReduced;
use PHPSemVerChecker\Registry\Registry;
use PHPSemVerChecker\Report\Report;

/**
 * Whiltelist elements removal analyzer.
 *
 * @package Magento\SemanticVersionChecker\Analyzer
 */
class DbSchemaWhitelistReductionAnalyzer implements AnalyzerInterface
{
    /**
     * Analyze content difference for schema whitelist.
     *
     * @param Registry $registryBefore
     * @param Registry $registryAfter
     * @return Report
     */
    public function analyze($registryBefore, $registryAfter): Report
    {
        $report = new Report();
        if (empty($registryBefore->getCurrentFile())) {
            return $report;
        }
        if (preg_match('/db_schema_whitelist\.json$/', $registryBefore->getCurrentFile())) {
            $whitelistBefore = json_decode(file_get_contents($registryBefore->getCurrentFile()), true);
            $whitelistAfter = json_decode(file_get_contents($registryAfter->getCurrentFile()), true);
            $diff = $this->compareWhitelists($whitelistBefore, $whitelistAfter);

            if (!empty($diff)) {
                $operation = new WhiteListReduced($registryAfter->getCurrentFile());
                $report->add('database', $operation);
            }
        }

        return $report;
    }

    /**
     * Compare whitelistst and build diff recursively to find deleted elements.
     *
     * @param array $before
     * @param array $after
     * @return array
     */
    private function compareWhitelists(array $before, array $after) : array
    {
        $diff = [];
        foreach ($before as $key => $value) {
            if (is_array($value) && isset($after[$key])) {
                $subdiff = $this->compareWhitelists($value, $after[$key]);
                if (!empty($subdiff)) {
                    $diff[$key] = $subdiff;
                }
            } elseif (!array_key_exists($key, $after)) {
                $diff[$key] = true;
            }
        }
        return $diff;
    }
}
