<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Analyzer\Factory;

use Magento\SemanticVersionChecker\Analyzer\Analyzer;
use Magento\SemanticVersionChecker\Analyzer\AnalyzerInterface;
use Magento\SemanticVersionChecker\Analyzer\DiXml\VirtualTypeAnalyzer;
use Magento\SemanticVersionChecker\ClassHierarchy\DependencyGraph;
use Magento\SemanticVersionChecker\DbSchemaReport;

/**
 * Build and DI XML File Analyzer.
 */
class DiAnalyzerFactory implements AnalyzerFactoryInterface
{
    /**
     * @param DependencyGraph|null $dependencyGraph
     * @return AnalyzerInterface
     */
    public function create(DependencyGraph $dependencyGraph = null): AnalyzerInterface
    {
        $report = new DbSchemaReport();
        $analyzers = [
            new VirtualTypeAnalyzer($report),
        ];

        return new Analyzer($analyzers);
    }
}
