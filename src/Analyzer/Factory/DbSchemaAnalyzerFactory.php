<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tools\SemanticVersionChecker\Analyzer\Factory;

use Magento\Tools\SemanticVersionChecker\Analyzer\Analyzer;
use Magento\Tools\SemanticVersionChecker\Analyzer\AnalyzerInterface;
use Magento\Tools\SemanticVersionChecker\Analyzer\DbSchemaAnalyzer;
use Magento\Tools\SemanticVersionChecker\Analyzer\DbSchemaWhitelistAnalyzer;
use Magento\Tools\SemanticVersionChecker\Analyzer\DbSchemaWhitelistReductionAnalyzer;

/**
 * Build and DB Schema File Analyzer
 */
class DbSchemaAnalyzerFactory implements AnalyzerFactoryInterface
{
    /**
     * @return AnalyzerInterface
     */
    public function create(): AnalyzerInterface
    {
        $analyzers = [
            new DbSchemaAnalyzer(),
            new DbSchemaWhitelistAnalyzer(),
            new DbSchemaWhitelistReductionAnalyzer(),
        ];

        return new Analyzer($analyzers);
    }
}
