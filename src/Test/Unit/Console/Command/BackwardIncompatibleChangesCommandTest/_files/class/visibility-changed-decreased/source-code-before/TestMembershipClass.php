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
    protected const PROTECTED_TO_PRIVATE_CONST = 'some_content';
    public const    PUBLIC_TO_PRIVATE_CONST    = 'some_content';
    public const    PUBLIC_TO_PROTECTED_CONST  = 'some_content';

    protected $protectedToPrivateProperty;
    public    $publicToPrivateProperty;
    public    $publicToProtectedProperty;

    /**
     * @param array $methodParam
     */
    protected function protectedToPrivateMembershipMethod(array $methodParam)
    {
    }

    /**
     * @param array $methodParam
     */
    public function publicToPrivateMembershipMethod(array $methodParam)
    {
    }

    /**
     * @param array $methodParam
     */
    public function publicToProtectedMembershipMethod(array $methodParam)
    {
    }
}
