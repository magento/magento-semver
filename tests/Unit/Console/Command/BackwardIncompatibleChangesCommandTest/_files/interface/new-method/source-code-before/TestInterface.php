<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Test\Vcs;

interface TestInterface
{
    /**
     * @param array $methodParam
     * @return mixed
     */
    public function testMembershipMethod(array $methodParam);

    /**
     * @api
     * @param array $methodParam
     * @return mixed
     */
    public function testExistingApiMethod(array $methodParam);
}
