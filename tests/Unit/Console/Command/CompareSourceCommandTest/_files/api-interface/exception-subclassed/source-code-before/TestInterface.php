<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Test\Vcs;

use Test\Vcs\Exceptions\TestParentException;

/**
 * @api
 */
interface TestInterface
{
    /**
     * @param string $param
     *
     * @throws TestParentException
     */
    public function exceptionSubclassed(string $param);
}
