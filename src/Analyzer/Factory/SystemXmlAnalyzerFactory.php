<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionCheckr\Analyzer\Factory;

use Magento\SemanticVersionCheckr\Analyzer\Analyzer;
use Magento\SemanticVersionCheckr\Analyzer\AnalyzerInterface;
use Magento\SemanticVersionCheckr\Analyzer\SystemXml\Analyzer as SystemXmlAnalyzer;
use Magento\SemanticVersionCheckr\DbSchemaReport;

/**
 * Builds an analyzer for analysis of <kbd>system.xml</kbd> files.
 */
class SystemXmlAnalyzerFactory implements AnalyzerFactoryInterface
{

    /**
     * @return AnalyzerInterface
     */
    public function create(): AnalyzerInterface
    {
        $report = new DbSchemaReport();
        $analyzers = [
            new SystemXmlAnalyzer($report),
        ];

        return new Analyzer($analyzers);
    }
}
