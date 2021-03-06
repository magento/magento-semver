<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// @codingStandardsIgnoreFile
namespace Magento\SemanticVersionChecker;

use PHPSemVerChecker\SemanticVersioning\Level;
use Magento\SemanticVersionChecker\Analyzer\EtSchemaAnalyzer;

class DbSchemaReport extends MergedReport
{
    /**
     * Report constructor.
     */
    public function __construct()
    {
        $levels = array_fill_keys(Level::asList(), []);
        parent::__construct();
        $this->differences['database'] = $levels;
        $this->differences['di'] = $levels;
        $this->differences['layout'] = $levels;
        $this->differences['system'] = $levels;
        $this->differences['xsd'] = $levels;
        $this->differences['less'] = $levels;
        $this->differences[EtSchemaAnalyzer::CONTEXT] = $levels;
    }
}
