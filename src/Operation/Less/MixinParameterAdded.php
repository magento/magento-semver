<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Operation\Less;

use PHPSemVerChecker\SemanticVersioning\Level;
use Magento\SemanticVersionChecker\Operation\AbstractOperation;

/**
 * When a <kbd>mixin parameter</kbd> was added.
 */
class MixinParameterAdded extends AbstractOperation
{
    /**
     * @var string
     */
    protected $code = 'M403';

    /**
     * @var int
     */
    protected $level = Level::MAJOR;

    /**
     * @var string
     */
    protected $reason = 'A parameter was added to the mixin-node';
}
