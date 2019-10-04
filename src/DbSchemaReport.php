<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// @codingStandardsIgnoreFile
namespace Magento\Tools\SemanticVersionChecker;

use PHPSemVerChecker\Report\Report as ReportAlias;
use PHPSemVerChecker\SemanticVersioning\Level;

class DbSchemaReport extends ReportAlias
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
    }
}
