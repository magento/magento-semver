<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker;

use PHPSemVerChecker\Report\Report;

/**
 * Report object whose contents can be set by client through constructor
 */
class InjectableReport extends Report
{
    /**
     * @param array $differences
     */
    public function __construct($differences = [])
    {
        if ($differences) {
            $this->differences = $differences;
        } else {
            parent::__construct();
        }
    }
}
