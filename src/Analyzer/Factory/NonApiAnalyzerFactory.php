<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionCheckr\Analyzer\Factory;

use Magento\SemanticVersionCheckr\Analyzer\AnalyzerInterface;
use Magento\SemanticVersionCheckr\Analyzer\ClassAnalyzer;
use Magento\SemanticVersionCheckr\Analyzer\InterfaceAnalyzer;
use Magento\SemanticVersionCheckr\Analyzer\NonApiAnalyzer;
use Magento\SemanticVersionCheckr\Analyzer\TraitAnalyzer;

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
