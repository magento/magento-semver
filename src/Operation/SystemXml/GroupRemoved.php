<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionCheckr\Operation\SystemXml;

use PHPSemVerChecker\SemanticVersioning\Level;
use Magento\SemanticVersionCheckr\Operation\AbstractOperation;

/**
 * When a <kbd>group</kbd> node was removed.
 */
class GroupRemoved extends AbstractOperation
{
    /**
     * @var string
     */
    protected $code = 'M305';

    /**
     * @var int
     */
    protected $level = Level::MAJOR;

    /**
     * @var string
     */
    protected $reason = 'A group-node was removed';
}
