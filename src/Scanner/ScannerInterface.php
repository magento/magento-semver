<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Scanner;

use PHPSemVerChecker\Registry\Registry;

/**
 * @api
 */
interface ScannerInterface
{

    /**
     * @param string $file
     */
    public function scan(string $file) : void;

    /**
     * @return Registry
     */
    public function getRegistry(): Registry;
}
