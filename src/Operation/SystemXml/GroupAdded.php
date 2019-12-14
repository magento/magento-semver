<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Operation\SystemXml;

use PHPSemVerChecker\SemanticVersioning\Level;
use Magento\SemanticVersionChecker\Operation\AbstractOperation;

/**
 * When a <kbd>group</kbd> node was added.
 */
class GroupAdded extends AbstractOperation
{
    /**
     * @var int
     */
    protected $code = 'M304';

    /**
     * @var int
     */
    protected $level = Level::MINOR;

    /**
     * @var string
     */
    protected $reason = 'A group-node was added';
}
