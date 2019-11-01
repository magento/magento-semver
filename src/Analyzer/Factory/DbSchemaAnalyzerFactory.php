<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionCheckr\Analyzer\Factory;

use Magento\SemanticVersionCheckr\Analyzer\Analyzer;
use Magento\SemanticVersionCheckr\Analyzer\AnalyzerInterface;
use Magento\SemanticVersionCheckr\Analyzer\DBSchema\DbSchemaForeignKeyAnalyzer;
use Magento\SemanticVersionCheckr\Analyzer\DBSchema\DbSchemaPrimaryKeyAnalyzer;
use Magento\SemanticVersionCheckr\Analyzer\DBSchema\DbSchemaUniqueKeyAnalyzer;
use Magento\SemanticVersionCheckr\Analyzer\DBSchema\DbSchemaTableAnalyzer;
use Magento\SemanticVersionCheckr\Analyzer\DBSchema\DbSchemaColumnAnalyzer;
use Magento\SemanticVersionCheckr\Analyzer\DBSchema\DbSchemaWhitelistAnalyzer;
use Magento\SemanticVersionCheckr\Analyzer\DBSchema\DbSchemaWhitelistReductionOrRemovalAnalyzer;
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
