<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Analyzer;

use Magento\SemanticVersionChecker\DbSchemaReport;
use PHPSemVerChecker\Registry\Registry;
use PHPSemVerChecker\Report\Report;

class Analyzer implements AnalyzerInterface
{
    /**
     * Compare with a destination registry (what the new source code is like).
     *
     * @param Registry $registryBefore
     * @param Registry $registryAfter
     * @return Report
     */
    public function analyze($registryBefore, $registryAfter)
    {
        $finalReport = new DbSchemaReport();

        /** @var AnalyzerInterface[] */
        $analyzers = [
            new ClassAnalyzer(),
            new InterfaceAnalyzer(),
            // @todo should moved in a extra analyzer for a better extendability.
            new DbSchemaAnalyzer(),
            new DbSchemaWhitelistAnalyzer(),
            new DbSchemaWhitelistReductionAnalyzer()
        ];

        foreach ($analyzers as $analyzer) {
            $report = $analyzer->analyze($registryBefore, $registryAfter);
            $finalReport->merge($report);
        }

        return $finalReport;
    }
}
