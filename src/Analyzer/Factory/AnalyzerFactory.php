<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Analyzer\Factory;

use Magento\SemanticVersionChecker\Analyzer\Analyzer;
use Magento\SemanticVersionChecker\Analyzer\AnalyzerInterface;
use Magento\SemanticVersionChecker\Analyzer\ClassAnalyzer;
use Magento\SemanticVersionChecker\Analyzer\InterfaceAnalyzer;
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
            new ClassAnalyzer(null, null, null, $dependencyGraph),
            new InterfaceAnalyzer(),
            new TraitAnalyzer(),
        ];

        return new Analyzer($analyzers);
    }
}
