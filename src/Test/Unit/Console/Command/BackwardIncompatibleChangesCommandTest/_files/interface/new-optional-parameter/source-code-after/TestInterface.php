<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Test\Vcs;

/**
 * @api
 */
interface TestInterface
{

    /**
     * @param array $methodParam
     * @param null $optionalParameter
     * @return mixed
     */
    public function testParameterAdded(array $methodParam, $optionalParameter = null);
}
