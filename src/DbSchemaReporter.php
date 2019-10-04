<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// @codingStandardsIgnoreFile
namespace Magento\Tools\SemanticVersionChecker;

use Magento\Tools\SemanticVersionChecker\Reporter\TableReporter;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DbSchemaReporter
 * @package Magento\Tools\SemanticVersionChecker
 */
class DbSchemaReporter extends TableReporter
{
    /**
     * Add db tables to report
     * @param OutputInterface $output
     */
    public function output(OutputInterface $output)
    {
        parent::output($output);

        // custome report types
        $this->outputReport($output, $this->report, 'database');
        $this->outputReport($output, $this->report, 'di');
    }
}
