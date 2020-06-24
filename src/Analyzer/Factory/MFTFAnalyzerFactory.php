<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Analyzer\Factory;

use Magento\SemanticVersionChecker\Analyzer\Analyzer;
use Magento\SemanticVersionChecker\Analyzer\AnalyzerInterface;
use Magento\SemanticVersionChecker\Analyzer\Mftf\ActionGroupAnalyzer;
use Magento\SemanticVersionChecker\Analyzer\Mftf\DataAnalyzer;
use Magento\SemanticVersionChecker\Analyzer\Mftf\MetadataAnalyzer;
use Magento\SemanticVersionChecker\Analyzer\Mftf\PageAnalyzer;
use Magento\SemanticVersionChecker\Analyzer\Mftf\SectionAnalyzer;
use Magento\SemanticVersionChecker\Analyzer\Mftf\TestAnalyzer;
use Magento\SemanticVersionChecker\ClassHierarchy\DependencyGraph;
use Magento\SemanticVersionChecker\MftfReport;

/**
 * Build Mftf analyzer
 */
class MFTFAnalyzerFactory implements AnalyzerFactoryInterface
{
    /**
     * @param DependencyGraph|null $dependencyGraph
     * @return AnalyzerInterface
     */
    public function create(DependencyGraph $dependencyGraph = null): AnalyzerInterface
    {
        $report = new MftfReport();
        $analyzers = [
            new DataAnalyzer($report),
            new MetadataAnalyzer($report),
            new PageAnalyzer($report),
            new SectionAnalyzer($report),
            new TestAnalyzer($report),
            new ActionGroupAnalyzer($report)
        ];

        return new Analyzer($analyzers);
    }
}
