<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Operation\Xsd;

use PHPSemVerChecker\SemanticVersioning\Level;
use Magento\SemanticVersionChecker\Operation\AbstractOperation;

/**
 * When an attribute was removed.
 */
class AttributeRemoved extends AbstractOperation
{
    /**
     * @var string
     */
    protected $code = 'M0138';

    /**
     * @var int
     */
    protected $level = Level::MAJOR;

    /**
     * @var string
     */
    protected $reason = 'An attribute was removed';
}
