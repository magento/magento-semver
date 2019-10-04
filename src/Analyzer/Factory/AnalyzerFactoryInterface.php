<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tools\SemanticVersionChecker\Analyzer\Factory;

use Magento\Tools\SemanticVersionChecker\Analyzer\AnalyzerInterface;

/**
 * Defines an interface for analyzer factory to build analyzer.
 */
interface AnalyzerFactoryInterface
{
    /**
     * @return AnalyzerInterface
     */
    public function create(): AnalyzerInterface;
}
