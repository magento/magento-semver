<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile
namespace Magento\SemanticVersionChecker;

use Exception;
use Magento\SemanticVersionChecker\Analyzer\Analyzer;
use Magento\SemanticVersionChecker\Filter\FilePatternFilter;
use Magento\SemanticVersionChecker\Finder\DbSchemaFinderDecorator;
use PHPSemVerChecker\Configuration\LevelMapping;
use PHPSemVerChecker\Filter\SourceFilter;
use PHPSemVerChecker\Report\Report;
use PHPSemVerChecker\SemanticVersioning\Level;

class ReportBuilder
{
    const REPORT_TYPE_ALL = 'all';

    const REPORT_TYPE_API = 'api';

    /** @var string */
    protected $includePatternsPath;
    /** @var string */
    protected $excludePatternsPath;
    protected $sourceBeforeDir;
    protected $sourceAfterDir;

    /**
     * @var ObjectBuilderContainer
     */
    protected $objectContainer;

    public function __construct($includePatternsPath, $excludePatternsPath, $sourceBeforeDir, $sourceAfterDir)
    {
        $this->includePatternsPath = $includePatternsPath;
        $this->excludePatternsPath = $excludePatternsPath;
        $this->sourceBeforeDir = $sourceBeforeDir;
        $this->sourceAfterDir = $sourceAfterDir;

        $this->objectContainer = new ObjectBuilderContainer();
    }

    /**
     * @return Report
     */
    public function makeCompleteVersionReport()
    {
        return $this->makeVersionReport();
    }

    /**
     * Set Magento's custom severity level overrides then build a report based on type
     *
     * @param string $reportType REPORT_TYPE_API|REPORT_TYPE_ALL
     * @return Report
     */
    protected function makeVersionReport()
    {
        $originalMapping = LevelMapping::$mapping;
        // Customize severity level of some @api changes
        LevelMapping::setOverrides(
            [
                'V015' => Level::MINOR, // Add public method
                'V016' => Level::MINOR, // Add protected method
                'V019' => Level::MINOR, // Add public property
                'V020' => Level::MINOR, // Add protected property
                'V034' => Level::MINOR, // Add public method to an interface
                'V060' => Level::MAJOR, // Public method parameter change
                'V063' => Level::MAJOR, // Public method parameter change
            ]
        );

        try {
            $report = $this->buildReport();
        } finally {
            // Restore original severity levels
            LevelMapping::setOverrides($originalMapping);
        }

        return $report;
    }

    /**
     * Create a report based on type
     *
     * @param string $reportType
     * @return Report
     * @throws Exception
     */
    protected function buildReport()
    {
        $fileIterator = new DbSchemaFinderDecorator();
        $sourceBeforeFiles = $fileIterator->findFromString($this->sourceBeforeDir, '', '');
        $sourceAfterFiles = $fileIterator->findFromString($this->sourceAfterDir, '', '');

        $scannerBefore = new ScannerRegistry($this->objectContainer->getAllScanner());
        $scannerAfter = new ScannerRegistry($this->objectContainer->getAllScanner());

        $filters = $this->getFilters($this->sourceBeforeDir, $this->sourceAfterDir);
        foreach ($filters as $filter) {
            // filters modify arrays by reference
            $filter->filter($sourceBeforeFiles, $sourceAfterFiles);
        }

        foreach ($sourceBeforeFiles as $file) {
            $scannerBefore->scanFile($file);
        }

        foreach ($sourceAfterFiles as $file) {
            $scannerAfter->scanFile($file);
        }

        $beforeRegistryList = $scannerBefore->getScannerRegistryList();
        $afterRegistryList = $scannerAfter->getScannerRegistryList();

        $analyzer = new Analyzer();
        $apiReport = $analyzer->analyze($beforeRegistryList['api'], $afterRegistryList['api']);

        $analyzer = new Analyzer();
        $allReport = $this->dampenNonApiReport(
            $analyzer->analyze($beforeRegistryList['full'], $afterRegistryList['full'])
        );

        return $allReport->merge($apiReport);
    }

    /**
     * Get filters for source files
     *
     * @param string $sourceBeforeDir
     * @param string $sourceAfterDir
     * @return array
     */
    protected function getFilters($sourceBeforeDir, $sourceAfterDir): array
    {
        $filters = [
            // always filter out files that are identical before and after
            new SourceFilter(),
            // process the include and exclude patterns
            new FilePatternFilter(
                $this->includePatternsPath,
                $this->excludePatternsPath,
                $sourceBeforeDir,
                $sourceAfterDir
            ),
        ];

        return $filters;
    }

    /**
     * Non-API changes are not bound by backwards incompatibility so set them to Patch-level
     *
     * @param Report $report
     * @return InjectableReport
     */
    protected function dampenNonApiReport(Report $report)
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
