<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Test\Vcs;

use Test\Vcs\Path\TestClass2;

interface TestInterface extends \Test\Vcs\Parent\ParentTestInterface
{
    /**
     * @return $this
     */
    public function testMethod();

    /**
     * @return TestClass2
     */
    public function testMethod2();

    /**
     * @inheritDoc
     */
    public function testMethodInherited();
}
