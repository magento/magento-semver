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
 * When a node was removed.
 */
class NodeRemoved extends AbstractOperation
{
    /**
     * @var string
     */
    protected $code = 'M0137';

    /**
     * @var int
     */
    protected $level = Level::MAJOR;

    /**
     * @var string
     */
    protected $reason = 'A node was removed';
}
