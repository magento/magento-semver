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
 * When a <kbd>section</kbd> node was removed.
 */
class SectionRemoved extends AbstractOperation
{
    /**
     * @var string
     */
    protected $code = 'MM307';

    /**
     * @var int
     */
    protected $level = Level::MAJOR;

    /**
     * @var string
     */
    protected $reason = 'a section-node was removed';
}
