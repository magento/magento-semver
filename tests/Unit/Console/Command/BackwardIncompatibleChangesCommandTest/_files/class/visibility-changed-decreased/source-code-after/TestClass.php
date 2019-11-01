<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Test\Vcs;

class TestClass
{
    private const   PROTECTED_TO_PRIVATE_CONST = 'some_content';
    private const   PUBLIC_TO_PRIVATE_CONST    = 'some_content';
    protected const PUBLIC_TO_PROTECTED_CONST  = 'some_content';

    private   $protectedToPrivateProperty;
    private   $publicToPrivateProperty;
    protected $publicToProtectedProperty;

    /**
     * @param array $methodParam
     */
    private function protectedToPrivateMethod(array $methodParam)
    {
    }

    /**
     * @param array $methodParam
     */
    private function publicToPrivateMethod(array $methodParam)
    {
    }

    /**
     * @param array $methodParam
     */
    protected function publicToProtectedMethod(array $methodParam)
    {
    }
}
