<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Test\Vcs;

class TestClass
{
    private const   PRIVATE_TO_PROTECTED_CONST = 'some_content';
    private const   PRIVATE_TO_PUBLIC_CONST    = 'some_content';
    protected const PROTECTED_TO_PUBLIC_CONST  = 'some_content';

    private   $privateToProtectedProperty;
    private   $privateToPublicProperty;
    protected $protectedToPublicProperty;

    /**
     * @param array $methodParam
     */
    private function privateToProtectedMethod(array $methodParam)
    {
    }

    /**
     * @param array $methodParam
     */
    private function privateToPublicMethod(array $methodParam)
    {
    }

    /**
     * @param array $methodParam
     */
    protected function protectedToPublicMethod(array $methodParam)
    {
    }
}
