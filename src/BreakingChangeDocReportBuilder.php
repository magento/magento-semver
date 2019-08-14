<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker;

use Exception;
use Magento\SemanticVersionChecker\Analyzer\ApiMembership\ApiMembershipAnalyzer;
use Magento\SemanticVersionChecker\Finder\DbSchemaFinderDecorator;
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
        $this->makeVersionReport();
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

    /**
     * Create BIC reports for API changes and existing code that gained or lost API membership
     * @return void
     * @throws Exception
     */
    protected function buildReport()
    {
        $fileIterator = new DbSchemaFinderDecorator();
        $sourceBeforeFiles = $fileIterator->findFromString($this->sourceBeforeDir, '', '');
        $sourceAfterFiles = $fileIterator->findFromString($this->sourceAfterDir, '', '');
        $scannerRegistryBefore = new ScannerRegistry($this->objectContainer->getAllScanner());
        $scannerRegistryAfter = new ScannerRegistry($this->objectContainer->getAllScanner());

        $filters = $this->getFilters($this->sourceBeforeDir, $this->sourceAfterDir);
        foreach ($filters as $filter) {
            $filter->filter($sourceBeforeFiles, $sourceAfterFiles);
        }

        foreach ($sourceBeforeFiles as $file) {
            $scannerRegistryBefore->scanFile($file);
        }

        foreach ($sourceAfterFiles as $file) {
            $scannerRegistryAfter->scanFile($file);
        }

        $beforeRegistryList = $scannerRegistryBefore->getScannerRegistryList();
        $afterRegistryList = $scannerRegistryAfter->getScannerRegistryList();

        $analyzer = new ApiMembershipAnalyzer();
        $analyzer->analyzeWithMembership(
            $beforeRegistryList['api'],
            $afterRegistryList['api'],
            $beforeRegistryList['full'],
            $afterRegistryList['full']
        );

        $this->changeReport = $analyzer->getBreakingChangeReport();
        $this->membershipReport = $analyzer->getApiMembershipReport();
    }
}
