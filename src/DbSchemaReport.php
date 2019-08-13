<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// @codingStandardsIgnoreFile
namespace Magento\SemanticVersionChecker;

use PHPSemVerChecker\SemanticVersioning\Level;

class DbSchemaReport extends \PHPSemVerChecker\Report\Report
{
    /**
     * Report constructor.
     */
    public function __construct()
    {
        $levels = array_fill_keys(Level::asList(), []);
        parent::__construct();
        $this->differences['database'] = $levels;
    }
}
