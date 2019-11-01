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
 * When a <kbd>system.xml</kbd> file is added.
 */
class FileAdded extends AbstractOperation
{
    /**
     * @var string
     */
    protected $code = 'M300';

    /**
     * @var int
     */
    protected $level = Level::MINOR;

    /**
     * @var string
     */
    protected $reason = 'System configuration file was added';
}
