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
     */
    public function publicMethod($paramA, $paramB)
    {
    }

    /**
     * @param string $paramA
     * @param string $paramB
     */
    protected function protectedMethod($paramA, $paramB)
    {
    }

    /**
     * @param string $paramA
     * @param string $paramB
     */
    private function privateMethod($paramA, $paramB)
    {
    }
}
