<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Analyzer\Factory;

use Magento\SemanticVersionChecker\Analyzer\AnalyzerInterface;
use Magento\SemanticVersionChecker\Analyzer\ClassAnalyzer;
use Magento\SemanticVersionChecker\Analyzer\InterfaceAnalyzer;
use Magento\SemanticVersionChecker\Analyzer\NonApiAnalyzer;
use Magento\SemanticVersionChecker\Analyzer\TraitAnalyzer;
use Magento\SemanticVersionChecker\ClassHierarchy\DependencyGraph;

/**
 * Implements a factory for {@link NonApiAnalyzer}.
 */
class NonApiAnalyzerFactory implements AnalyzerFactoryInterface
{
    /**
     * @param DependencyGraph|null $dependencyGraph
     * @return AnalyzerInterface
     */
    public function create(DependencyGraph $dependencyGraph = null): AnalyzerInterface
    {
        $analyzers = [
            new ClassAnalyzer(null, null, null, $dependencyGraph),
            new InterfaceAnalyzer(null, null, null, $dependencyGraph),
            new TraitAnalyzer(),
        ];

        return new NonApiAnalyzer($analyzers);
    }
}
