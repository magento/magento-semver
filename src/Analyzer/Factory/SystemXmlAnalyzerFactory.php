<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Analyzer\Factory;

use Magento\SemanticVersionChecker\Analyzer\Analyzer;
use Magento\SemanticVersionChecker\Analyzer\AnalyzerInterface;
use Magento\SemanticVersionChecker\Analyzer\SystemXml\Analyzer as SystemXmlAnalyzer;
use Magento\SemanticVersionChecker\ClassHierarchy\DependencyGraph;
use Magento\SemanticVersionChecker\DbSchemaReport;

/**
 * Builds an analyzer for analysis of <kbd>system.xml</kbd> files.
 */
class SystemXmlAnalyzerFactory implements AnalyzerFactoryInterface
{

    /**
     * @param DependencyGraph|null $dependencyGraph
     * @return AnalyzerInterface
     */
    public function create(DependencyGraph $dependencyGraph = null): AnalyzerInterface
    {
        $report = new DbSchemaReport();
        $analyzers = [
            new SystemXmlAnalyzer($report),
        ];

        return new Analyzer($analyzers);
    }
}