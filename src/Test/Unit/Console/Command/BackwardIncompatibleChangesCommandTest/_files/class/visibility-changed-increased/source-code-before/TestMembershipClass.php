<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Test\Vcs;

/**
 * Class TestMembershipClass
 *
 * @api
 */
class TestMembershipClass
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
    private function privateToProtectedMembershipMethod(array $methodParam)
    {
    }

    /**
     * @param array $methodParam
     */
    private function privateToPublicMembershipMethod(array $methodParam)
    {
    }

    /**
     * @param array $methodParam
     */
    protected function protectedToPublicMembershipMethod(array $methodParam)
    {
    }
}
