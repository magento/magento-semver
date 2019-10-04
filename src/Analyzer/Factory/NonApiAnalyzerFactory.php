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
