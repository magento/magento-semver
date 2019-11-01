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
    private const   PROTECTED_TO_PRIVATE_CONST = 'some_content';
    private const   PUBLIC_TO_PRIVATE_CONST    = 'some_content';
    protected const PUBLIC_TO_PROTECTED_CONST  = 'some_content';

    private   $protectedToPrivateProperty;
    private   $publicToPrivateProperty;
    protected $publicToProtectedProperty;

    /**
     * @param array $methodParam
     */
    private function protectedToPrivateMembershipMethod(array $methodParam)
    {
    }

    /**
     * @param array $methodParam
     */
    private function publicToPrivateMembershipMethod(array $methodParam)
    {
    }

    /**
     * @param array $methodParam
     */
    protected function publicToProtectedMembershipMethod(array $methodParam)
    {
    }
}
