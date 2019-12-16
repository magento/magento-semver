<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Analyzer;

use Magento\SemanticVersionChecker\DbSchemaReport;
use PHPSemVerChecker\Registry\Registry;
use PHPSemVerChecker\Report\Report;

class Analyzer implements AnalyzerInterface
{

    /**
     * @var array|AnalyzerInterface[]
     */
    private $analyzers;

    /**
     * Analyzer constructor.
     * @param AnalyzerInterface[] $analyzers
     */
    public function __construct(array $analyzers)
    {
        $this->analyzers = $analyzers;
    }

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
        foreach ($this->analyzers as $analyzer) {
            $report = $analyzer->analyze($registryBefore, $registryAfter);
            $finalReport->merge($report);
        }

        return $finalReport;
    }
}
