<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Test\Vcs;

interface TestInterface extends \Test\Vcs\Parent\ParentTestInterface
{
    /**
     * @return $this;
     */
    public function testMethod();

    /**
     * @return \Test\Vcs\Path\TestClass2
     */
    public function testMethod2();

    /**
     * @return null|int|\Test\Vcs\Path\TestClass2
     */
    public function testMethodInherited();
}
