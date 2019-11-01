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
 * When an optional node was added.
 */
class OptionalNodeAdded extends AbstractOperation
{
    /**
     * @var array
     */
    protected $code = 'M0133';

    /**
     * @var int
     */
    protected $level = Level::MINOR;

    /**
     * @var string
     */
    protected $reason = 'An optional node was added';
}
