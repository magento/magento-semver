<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Test\Vcs;

class TestClass extends \Test\Vcs\Parent\ParentTestClass implements \Test\Vcs\InterfaceNs\TestInterface
{
    /**
     * @return $this;
     */
    public function testMethod()
    {
        return $this;
    }

    /**
     * @return \Test\Vcs\Path\TestClass2
     */
    public function testMethod2()
    {
        return null;
    }

    /**
     * @return null|int|\Test\Vcs\Path\TestClass2
     */
    public function testMethodInherited()
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function testMethodInherited2()
    {
        return null;
    }

    /**
     * @return null|int|\Test\Vcs\Path\TestClass2
     */
    public function testInterfaceMethod1()
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function testInterfaceMethod2()
    {
        return null;
    }
}
