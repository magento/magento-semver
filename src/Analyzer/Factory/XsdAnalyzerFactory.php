<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Analyzer\Factory;

use Magento\SemanticVersionChecker\Analyzer\Analyzer;
use Magento\SemanticVersionChecker\Analyzer\AnalyzerInterface;
use Magento\SemanticVersionChecker\Analyzer\Xsd\Analyzer as XsdAnalyzer;
use Magento\SemanticVersionChecker\DbSchemaReport;

/**
 * Build XSD file analyzer.
 */
class XsdAnalyzerFactory implements AnalyzerFactoryInterface
{
    /**
     * @return AnalyzerInterface
     */
    public function create(): AnalyzerInterface
    {
        $report    = new DbSchemaReport();
        $analyzers = [
            new XsdAnalyzer($report),
        ];

        return new Analyzer($analyzers);
    }
}
