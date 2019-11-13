<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Operation\SystemXml;

use Magento\SemanticVersionChecker\Operation\AbstractOperation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * When a <kbd>field</kbd> node is added.
 */
class FieldAdded extends AbstractOperation
{
    /**
     * @var string
     */
    protected $code = 'M302';

    /**
     * @var int
     */
    protected $level = Level::MINOR;

    /**
     * @var string
     */
    protected $reason = 'A field-node was added';
}
