<?php
/**
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
     * @api
     * @param array $methodParam
     * @return array
     */
    public function testRemoveMethod(array $methodParam);

    /**
     * @api
     * @param array $methodParam
     * @return array
     */
    public function testMembershipMethod(array $methodParam);
}
