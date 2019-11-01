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
 * When a <kbd>field</kbd> node is removed.
 */
class FieldRemoved extends AbstractOperation
{
    /**
     * @var string
     */
    protected $code = 'M303';

    /**
     * @var int
     */
    protected $level = Level::MAJOR;

    /**
     * @var string
     */
    protected $reason = 'A field-node was removed';
}
