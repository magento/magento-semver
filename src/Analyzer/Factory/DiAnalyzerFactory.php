<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tools\SemanticVersionChecker\Analyzer\Factory;

use Magento\Tools\SemanticVersionChecker\Analyzer\Analyzer;
use Magento\Tools\SemanticVersionChecker\Analyzer\AnalyzerInterface;
use Magento\Tools\SemanticVersionChecker\Analyzer\DiXml\VirtualTypeAnalyzer;
use Magento\Tools\SemanticVersionChecker\DbSchemaReport;

/**
 * Build and DI XML File Analyzer.
 */
class DiAnalyzerFactory implements AnalyzerFactoryInterface
{
    /**
     * @return AnalyzerInterface
     */
    public function create(): AnalyzerInterface
    {
        $report = new DbSchemaReport();
        $analyzers = [
            new VirtualTypeAnalyzer($report),
        ];

        return new Analyzer($analyzers);
    }
}
