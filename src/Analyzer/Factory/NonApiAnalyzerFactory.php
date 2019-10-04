<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tools\SemanticVersionChecker\Analyzer\Factory;

use Magento\Tools\SemanticVersionChecker\Analyzer\AnalyzerInterface;
use Magento\Tools\SemanticVersionChecker\Analyzer\ClassAnalyzer;
use Magento\Tools\SemanticVersionChecker\Analyzer\InterfaceAnalyzer;
use Magento\Tools\SemanticVersionChecker\Analyzer\NonApiAnalyzer;
use Magento\Tools\SemanticVersionChecker\Analyzer\TraitAnalyzer;

/**
 * Implements a factory for {@link NonApiAnalyzer}.
 */
class NonApiAnalyzerFactory implements AnalyzerFactoryInterface
{
    /**
     * @return AnalyzerInterface
     */
    public function create(): AnalyzerInterface
    {
        $analyzers = [
            new ClassAnalyzer(),
            new InterfaceAnalyzer(),
            new TraitAnalyzer(),
        ];

        return new NonApiAnalyzer($analyzers);
    }
}
