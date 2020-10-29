<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Analyzer\Factory;

use Magento\SemanticVersionChecker\Analyzer\Analyzer;
use Magento\SemanticVersionChecker\Analyzer\AnalyzerInterface;
use Magento\SemanticVersionChecker\Analyzer\ApiClassAnalyzer;
use Magento\SemanticVersionChecker\Analyzer\ApiInterfaceAnalyzer;
use Magento\SemanticVersionChecker\Analyzer\TraitAnalyzer;
use Magento\SemanticVersionChecker\ClassHierarchy\DependencyGraph;

/**
 * Builds a PHP File Analyzer
 */
class AnalyzerFactory implements AnalyzerFactoryInterface
{
    /**
     * @param DependencyGraph|null $dependencyGraph
     * @return AnalyzerInterface
     */
    public function create(DependencyGraph $dependencyGraph = null): AnalyzerInterface
    {
        $analyzers = [
            new ApiClassAnalyzer(null, null, null, $dependencyGraph),
            new ApiInterfaceAnalyzer(null, null, null, $dependencyGraph),
            new TraitAnalyzer(),
        ];

        return new Analyzer($analyzers);
    }
}
