<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionCheckr\Analyzer\Factory;

use Magento\SemanticVersionCheckr\Analyzer\Analyzer;
use Magento\SemanticVersionCheckr\Analyzer\AnalyzerInterface;
use Magento\SemanticVersionCheckr\Analyzer\DiXml\VirtualTypeAnalyzer;
use Magento\SemanticVersionCheckr\DbSchemaReport;

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
