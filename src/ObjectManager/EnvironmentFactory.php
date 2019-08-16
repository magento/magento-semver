<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\ObjectManager;

use Magento\Framework\App\ObjectManager\Environment\Developer;

class EnvironmentFactory extends \Magento\Framework\App\EnvironmentFactory
{
    public function createEnvironment()
    {
        return new Developer($this);
    }
}
