<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Analyzer\Factory;

use Magento\SemanticVersionChecker\Analyzer\Analyzer;
use Magento\SemanticVersionChecker\Analyzer\AnalyzerInterface;
use Magento\SemanticVersionChecker\Analyzer\DbSchemaAnalyzer;
use Magento\SemanticVersionChecker\Analyzer\DbSchemaWhitelistAnalyzer;
use Magento\SemanticVersionChecker\Analyzer\DbSchemaWhitelistReductionAnalyzer;

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
