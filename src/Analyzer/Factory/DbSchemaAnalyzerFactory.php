<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Analyzer\Factory;

use Magento\SemanticVersionChecker\Analyzer\Analyzer;
use Magento\SemanticVersionChecker\Analyzer\AnalyzerInterface;
use Magento\SemanticVersionChecker\Analyzer\DBSchema\DbSchemaForeignKeyAnalyzer;
use Magento\SemanticVersionChecker\Analyzer\DBSchema\DbSchemaPrimaryKeyAnalyzer;
use Magento\SemanticVersionChecker\Analyzer\DBSchema\DbSchemaUniqueKeyAnalyzer;
use Magento\SemanticVersionChecker\Analyzer\DBSchema\DbSchemaTableAnalyzer;
use Magento\SemanticVersionChecker\Analyzer\DBSchema\DbSchemaColumnAnalyzer;
use Magento\SemanticVersionChecker\Analyzer\DBSchema\DbSchemaWhitelistAnalyzer;
use Magento\SemanticVersionChecker\Analyzer\DBSchema\DbSchemaWhitelistReductionOrRemovalAnalyzer;
use PHPSemVerChecker\Report\Report;

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
        $report = new Report();
        $analyzers = [
            new DbSchemaTableAnalyzer($report),
            new DbSchemaColumnAnalyzer($report),
            new DbSchemaForeignKeyAnalyzer($report),
            new DbSchemaPrimaryKeyAnalyzer($report),
            new DbSchemaUniqueKeyAnalyzer($report),
            new DbSchemaWhitelistAnalyzer($report),
            new DbSchemaWhitelistReductionOrRemovalAnalyzer($report),
        ];

        return new Analyzer($analyzers);
    }
}
