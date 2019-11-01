<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionCheckr\Operation\Xsd;

use PHPSemVerChecker\SemanticVersioning\Level;
use Magento\SemanticVersionCheckr\Operation\AbstractOperation;

/**
 * When an optional attribute was added
 */
class OptionalAttributeAdded extends AbstractOperation
{
    /**
     * @var string
     */
    protected $code = 'M0134';

    /**
     * @var int
     */
    protected $level = Level::MINOR;

    /**
     * @var string
     */
    protected $reason = 'An optional attribute was added';
}
