<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Test\Vcs;

use Test\Vcs\Exceptions\TestChildException;

/**
 * @api
 */
interface TestInterface
{
    /**
     * @param string $param
     *
     * @throws TestChildException
     */
    public function exceptionSubclassed(string $param);
}
