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
class TestClass
{
    /**
     * @param string $paramA
     * @param string $paramB
     * @param string|null $paramC
     */
    public function publicMethod($paramA, $paramB, $paramC = null)
    {
    }

    /**
     * @param string $paramA
     * @param string $paramB
     * @param string|null $paramC
     */
    protected function protectedMethod($paramA, $paramB, $paramC = null)
    {
    }

    /**
     * @param string $paramA
     * @param string $paramB
     * @param string|null $paramC
     */
    private function privateMethod($paramA, $paramB, $paramC = null)
    {
    }
}
