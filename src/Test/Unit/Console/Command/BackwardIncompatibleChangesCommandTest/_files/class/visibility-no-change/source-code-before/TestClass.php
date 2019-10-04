<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Test\Vcs;

class TestClass
{
    private const   PRIVATE_CONST   = 'some_content';
    protected const PROTECTED_CONST = 'some_content';
    public const    PUBLIC_CONST    = 'some_content';

    private   $privateProperty;
    protected $protectedProperty;
    public    $publicProperty;

    /**
     * @param array $methodParam
     */
    private function privateMethod(array $methodParam)
    {
    }

    /**
     * @param array $methodParam
     */
    protected function protectedMethod(array $methodParam)
    {
    }

    /**
     * @param array $methodParam
     */
    public function publicMethod(array $methodParam)
    {
    }
}
