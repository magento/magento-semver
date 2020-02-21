<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// @codingStandardsIgnoreFile
namespace Magento\SemanticVersionChecker;

use PHPSemVerChecker\Report\Report as ReportAlias;
use PHPSemVerChecker\SemanticVersioning\Level;

class DbSchemaReport extends ReportAlias
{
    public static $contexts = [
        'class',
        'function',
        'interface',
        'trait',
        'database',
        'di',
        'layout',
        'system',
        'xsd',
        'less'
    ];

    /**
     * Report constructor.
     */
    public function __construct()
    {
        $levels = array_fill_keys(Level::asList(), []);
        parent::__construct();
        foreach (static::$contexts as $context) {
            $this->differences[$context] = $levels;
        }
    }
}
