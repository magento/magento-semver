<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Test\Vcs;

trait TestTrait
{
    /**
     * @param string $paramA
     */
    public function publicMethod($paramA)
    {
    }

    /**
     * @param string $paramA
     */
    protected function protectedMethod($paramA)
    {
    }

    /**
     * @param string $paramA
     */
    private function privateMethod($paramA)
    {
    }
}
