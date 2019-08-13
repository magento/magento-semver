<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker;

use Magento\SemanticVersionChecker\Analyzer\ApiMembership\ApiMembershipAnalyzer;
use Magento\SemanticVersionChecker\Finder\DbSchemaFinderDecorator;
use Magento\SemanticVersionChecker\Scanner\DbSchemaScannerDecorator;
use PHPSemVerChecker\Report\Report;

class BreakingChangeDocReportBuilder extends ReportBuilder
{
    /**
     * @var Report
     */
    protected $changeReport;

    /**
     * @var Report
     */
    protected $membershipReport;

    /**
     * @return void
     */
    public function makeCompleteVersionReport()
    {
        $this->makeVersionReport(static::REPORT_TYPE_API);
    }

    /**
     * Create BIC reports for API changes and existing code that gained or lost API membership
     *
     * @param string $ignoredReportType
     * @return void
     * @throws \Exception
     */
    protected function buildReport($ignoredReportType = null)
    {
        $fileIterator = new DbSchemaFinderDecorator();
        $sourceBeforeFiles = $fileIterator->findFromString($this->sourceBeforeDir, '', '');
        $sourceAfterFiles = $fileIterator->findFromString($this->sourceAfterDir, '', '');

        $apiScannerBefore = new DbSchemaScannerDecorator(static::REPORT_TYPE_API);
        $apiScannerAfter = new DbSchemaScannerDecorator(static::REPORT_TYPE_API);

        $fullScannerBefore = new DbSchemaScannerDecorator(static::REPORT_TYPE_ALL);
        $fullScannerAfter = new DbSchemaScannerDecorator(static::REPORT_TYPE_ALL);

        $filters = $this->getFilters($this->sourceBeforeDir, $this->sourceAfterDir);
        foreach ($filters as $filter) {
            $filter->filter($sourceBeforeFiles, $sourceAfterFiles);
        }

        foreach ($sourceBeforeFiles as $file) {
            $fullScannerBefore->scan($file);
            $apiScannerBefore->scan($file);
        }

        foreach ($sourceAfterFiles as $file) {
            $fullScannerAfter->scan($file);
            $apiScannerAfter->scan($file);
        }

        $apiRegistryBefore = $apiScannerBefore->getRegistry();
        $apiRegistryAfter = $apiScannerAfter->getRegistry();

        $fullRegistryBefore = $fullScannerBefore->getRegistry();
        $fullRegistryAfter = $fullScannerAfter->getRegistry();

        $analyzer = new ApiMembershipAnalyzer();
        $analyzer->analyzeWithMembership(
            $apiRegistryBefore,
            $apiRegistryAfter,
            $fullRegistryBefore,
            $fullRegistryAfter
        );

        $this->changeReport = $analyzer->getBreakingChangeReport();
        $this->membershipReport = $analyzer->getApiMembershipReport();
    }

    /**
     * Get the report of changes made to the existing APIs
     *
     * @return Report
     */
    public function getBreakingChangeReport()
    {
        return $this->changeReport;
    }

    /**
     * Get the report of changes made to API membership (items that gained/lost API status)
     *
     * @return Report
     */
    public function getApiMembershipReport()
    {
        return $this->membershipReport;
    }
}
