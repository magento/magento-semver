<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Test\Vcs\InterfaceNs;

interface TestInterface
{
    /**
     * @return null|int|\Test\Vcs\Path\TestClass2
     */
    public function testInterfaceMethod1();

    /**
     * @return null|int|\Test\Vcs\Path\TestClass2
     */
    public function testInterfaceMethod2();
}
