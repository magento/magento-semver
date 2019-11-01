<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Test\Vcs;

class TestClass
{
    protected const PRIVATE_TO_PROTECTED_CONST = 'some_content';
    public const    PRIVATE_TO_PUBLIC_CONST    = 'some_content';
    public const    PROTECTED_TO_PUBLIC_CONST  = 'some_content';

    protected $privateToProtectedProperty;
    public    $privateToPublicProperty;
    public    $protectedToPublicProperty;

    /**
     * @param array $methodParam
     */
    protected function privateToProtectedMethod(array $methodParam)
    {
    }

    /**
     * @param array $methodParam
     */
    public function privateToPublicMethod(array $methodParam)
    {
    }

    /**
     * @param array $methodParam
     */
    public function protectedToPublicMethod(array $methodParam)
    {
    }
}
