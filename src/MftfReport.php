<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker;

use PHPSemVerChecker\SemanticVersioning\Level;

class MftfReport extends MergedReport
{
    public const MFTF_REPORT_CONTEXT = 'mftf';
    /**
     * Report constructor.
     */
    public function __construct()
    {
        $levels = array_fill_keys(Level::asList(), []);
        parent::__construct();
        $this->differences[self::MFTF_REPORT_CONTEXT] = $levels;
    }
}
