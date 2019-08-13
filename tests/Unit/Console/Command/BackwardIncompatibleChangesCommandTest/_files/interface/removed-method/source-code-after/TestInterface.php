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
     * @api
     * @param array $methodParam
     * @return array
     */
    public function testExistingApiMethod(array $methodParam);

    /**
     * @param array $methodParam
     * @return array
     */
    public function testMembershipMethod(array $methodParam);
}
