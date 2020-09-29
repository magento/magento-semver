<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Test\Vcs\Parent;

interface ParentTestInterface
{
    /**
     * @return null|int|\Test\Vcs\Path\TestClass2
     */
    public function testMethodInherited();

    /**
     * @return null|int|\Test\Vcs\Path\TestClass2
     */
    public function testMethodInherited2();
}
