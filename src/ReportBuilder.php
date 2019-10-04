<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile
namespace Magento\Tools\SemanticVersionChecker;

use Magento\Tools\SemanticVersionChecker\Analyzer\AnalyzerFactoryInterface;
use Magento\Tools\SemanticVersionChecker\Analyzer\AnalyzerInterface;
use Magento\Tools\SemanticVersionChecker\Analyzer\Factory\AnalyzerFactory;
use Magento\Tools\SemanticVersionChecker\Analyzer\Factory\DbSchemaAnalyzerFactory;
use Magento\Tools\SemanticVersionChecker\Analyzer\Factory\DiAnalyzerFactory;
use Magento\Tools\SemanticVersionChecker\Analyzer\Factory\LayoutAnalyzerFactory;
use Magento\Tools\SemanticVersionChecker\Analyzer\Factory\NonApiAnalyzerFactory;
use Magento\Tools\SemanticVersionChecker\Filter\FilePatternFilter;
use Magento\Tools\SemanticVersionChecker\Filter\SourceFilter;
use Magento\Tools\SemanticVersionChecker\Finder\FinderDecorator;
use Magento\Tools\SemanticVersionChecker\Finder\FinderDecoratorFactory;
use Magento\Tools\SemanticVersionChecker\Scanner\ScannerRegistryFactory;
use PHPSemVerChecker\Configuration\LevelMapping;
use PHPSemVerChecker\Report\Report;
use PHPSemVerChecker\SemanticVersioning\Level;

class ReportBuilder
{

    /** @var string */
    protected $includePatternsPath;

    /** @var string */
    protected $excludePatternsPath;

    /** @var string */
    protected $sourceBeforeDir;

    /** @var string */
    protected $sourceAfterDir;

    /**
     * Define analyzer factory list for the different report types.
     * @var array
     */
    private $analyzerList = [
        ReportTypes::API => AnalyzerFactory::class,
        ReportTypes::ALL => NonApiAnalyzerFactory::class,
        ReportTypes::DB_SCHEMA => DbSchemaAnalyzerFactory::class,
        ReportTypes::DI_XML => DiAnalyzerFactory::class,
        ReportTypes::LAYOUT_XML => LayoutAnalyzerFactory::class,
    ];

    /**
     * Constructor.
     *
     * @param string $includePatternsPath
     * @param string $excludePatternsPath
     * @param string $sourceBeforeDir
     * @param string $sourceAfterDir
     */
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
        return $this->makeVersionReport();
    }

    /**
     * Set Magento's custom severity level overrides then build a report based on type
     *
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
                'V047' => Level::MINOR, // Add public method to trait
                'V048' => Level::MINOR, // Add protected method to trait
                'V057' => Level::MINOR, // Add private method to trait
                'V059' => Level::MAJOR, // App method parameter to private method of trait
                'V060' => Level::MAJOR, // Public class method parameter change
                'V063' => Level::MAJOR, // Public interface method parameter change
                'V064' => Level::MAJOR, // Public trait method parameter change
                'V066' => Level::MAJOR, // Private trait method parameter change
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
     * @return Report
     * @throws Exception
     */
    protected function buildReport()
    {
        $finderDecoratorFactory = new FinderDecoratorFactory();
        /** @var FinderDecorator $fileIterator */
        $fileIterator = $finderDecoratorFactory->create();
        $sourceBeforeFiles = $fileIterator->findFromString($this->sourceBeforeDir, '', '');
        $sourceAfterFiles = $fileIterator->findFromString($this->sourceAfterDir, '', '');

        $scannerRegistryFactory = new ScannerRegistryFactory();
        $scannerBefore = new ScannerRegistry($scannerRegistryFactory->create());
        $scannerAfter = new ScannerRegistry($scannerRegistryFactory->create());

        foreach ($this->getFilters($this->sourceBeforeDir, $this->sourceAfterDir) as $filter) {
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

        $report = null;

        /**
         * @var AnalyzerFactoryInterface $factory
         */
        foreach ($this->analyzerList as $reportType => $factory) {
            /** @var AnalyzerInterface $analyzer */
            $analyzer = (new $factory())->create();
            $tmpReport = $analyzer->analyze(
                $beforeRegistryList[$reportType],
                $afterRegistryList[$reportType]
            );

            if ($report === null) {
                $report = $tmpReport;
            } else {
                /** @var  Report $report */
                $report = $report->merge($tmpReport);
            }
        }

        return $report;
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

}
