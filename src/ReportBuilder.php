<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// @codingStandardsIgnoreFile
namespace Magento\SemanticVersionChecker;

use Magento\SemanticVersionChecker\Analyzer\Analyzer;
use Magento\SemanticVersionChecker\Filter\FilePatternFilter;
use Magento\SemanticVersionChecker\Finder\DbSchemaFinderDecorator;
use Magento\SemanticVersionChecker\Scanner\DbSchemaScannerDecorator;
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

    public function __construct($includePatternsPath, $excludePatternsPath, $sourceBeforeDir, $sourceAfterDir)
    {
        $this->includePatternsPath = $includePatternsPath;
        $this->excludePatternsPath = $excludePatternsPath;
        $this->sourceBeforeDir = $sourceBeforeDir;
        $this->sourceAfterDir = $sourceAfterDir;
    }

    /**
     * @return Report
     */
    public function makeCompleteVersionReport()
    {
        $apiReport = $this->makeVersionReport(self::REPORT_TYPE_API);
        $allReport = $this->dampenNonApiReport(
            $this->makeVersionReport(self::REPORT_TYPE_ALL)
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
    protected function getFilters($sourceBeforeDir, $sourceAfterDir)
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
            )
        ];

        return $filters;
    }

    /**
     * Set Magento's custom severity level overrides then build a report based on type
     *
     * @param string $reportType REPORT_TYPE_API|REPORT_TYPE_ALL
     * @return Report
     */
    protected function makeVersionReport($reportType)
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
            $report = $this->buildReport($reportType);
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
     * @throws \Exception
     */
    protected function buildReport($reportType)
    {
        $fileIterator = new DbSchemaFinderDecorator();
        $sourceBeforeFiles = $fileIterator->findFromString($this->sourceBeforeDir, '', '');
        $sourceAfterFiles = $fileIterator->findFromString($this->sourceAfterDir, '', '');

        $scannerBefore = new DbSchemaScannerDecorator($reportType);
        $scannerAfter = new DbSchemaScannerDecorator($reportType);

        $filters = $this->getFilters($this->sourceBeforeDir, $this->sourceAfterDir);
        foreach ($filters as $filter) {
            // filters modify arrays by reference
            $filter->filter($sourceBeforeFiles, $sourceAfterFiles);
        }

        foreach ($sourceBeforeFiles as $file) {
            $scannerBefore->scan($file);
        }

        foreach ($sourceAfterFiles as $file) {
            $scannerAfter->scan($file);
        }

        $registryBefore = $scannerBefore->getRegistry();
        $registryAfter = $scannerAfter->getRegistry();

        $analyzer = new Analyzer();
        return $analyzer->analyze($registryBefore, $registryAfter);
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
