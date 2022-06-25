<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Test\Vcs;

use Test\Vcs\Exceptions\TestChildException;
use Test\Vcs\Exceptions\TestParentException;

interface TestInterface
{
    /**
     * @param string $param
     *
     * @throws TestChildException
     * @throws TestParentException
     */
    public function exceptionSuperclassAdded(string $param);
}
