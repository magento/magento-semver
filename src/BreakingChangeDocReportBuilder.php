<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionCheckr;

use Magento\SemanticVersionCheckr\Analyzer\ApiMembership\ApiMembershipAnalyzer;
use Magento\SemanticVersionCheckr\Finder\FinderDecorator;
use Magento\SemanticVersionCheckr\Finder\FinderDecoratorFactory;
use Magento\SemanticVersionCheckr\Scanner\ObjectBuilderContainer;
use Magento\SemanticVersionCheckr\Scanner\ScannerRegistryFactory;
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
        $finderDecoratorFactory = new FinderDecoratorFactory();

        /** @var FinderDecorator $fileIterator */
        $fileIterator = $finderDecoratorFactory->create();
        $sourceBeforeFiles = $fileIterator->findFromString($this->sourceBeforeDir, '', '');
        $sourceAfterFiles = $fileIterator->findFromString($this->sourceAfterDir, '', '');

        $scannerRegistryFactory = new  ScannerRegistryFactory();
        $scannerRegistryBefore = new ScannerRegistry($scannerRegistryFactory->create());
        $scannerRegistryAfter = new ScannerRegistry($scannerRegistryFactory->create());

        foreach ($this->getFilters($this->sourceBeforeDir, $this->sourceAfterDir) as $filter) {
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
            $beforeRegistryList[ReportTypes::API],
            $afterRegistryList[ReportTypes::API],
            $beforeRegistryList[ReportTypes::ALL],
            $afterRegistryList[ReportTypes::ALL]
        );

        $this->changeReport = $analyzer->getBreakingChangeReport();
        $this->membershipReport = $analyzer->getApiMembershipReport();
    }
}
