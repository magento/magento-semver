<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Analyzer;

use Magento\SemanticVersionChecker\InjectableReport;
use PHPSemVerChecker\Report\Report;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * Defines an analyzer compound for non-API entities.
 *
 * Note that dampening the report as we do it here is actually at the wrong position. There should be specialized
 * analyzer classes that report the appropriate change levels for non-API entities (e.g. in case we decide to handle
 * specific cases differently).
 */
class NonApiAnalyzer extends Analyzer
{

    /**
     * @inheritDoc
     */
    public function analyze($registryBefore, $registryAfter)
    {
        //let parent do the analysis
        $report = parent::analyze($registryBefore, $registryAfter);

        //and dampen the report
        return $this->dampenReport($report);
    }

    /**
     * Non-API changes are not bound by backwards incompatibility so set them to Patch-level.
     *
     * @param Report $report
     * @return Report
     */
    private function dampenReport(Report $report): Report
    {
        $dampenedDifferences = $report->getDifferences();

        foreach ($dampenedDifferences as $context => $levels) {
            $dampenedDifferences[$context][Level::PATCH] = array_merge(
                $dampenedDifferences[$context][Level::MAJOR],
                $dampenedDifferences[$context][Level::MINOR],
                $dampenedDifferences[$context][Level::PATCH]
            );
            $dampenedDifferences[$context][Level::MINOR] = [];
            $dampenedDifferences[$context][Level::MAJOR] = [];
        }

        return new InjectableReport($dampenedDifferences);
    }
}
