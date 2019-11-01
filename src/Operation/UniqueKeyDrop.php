<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionCheckr\Operation;

use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Property;
use PHPSemVerChecker\Operation\Operation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * When unique key dropped
 */
class UniqueKeyDrop extends Operation
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = 'M209';

    /**
     * Change level.
     *
     * @var int
     */
    protected $level = Level::MAJOR;

    /**
     * Operation message.
     *
     * @var string
     */
    protected $reason = 'Unique key was removed';

    /**
     * File path before changes.
     *
     * @var string
     */
    protected $fileBefore;

    /**
     * Property context before changes.
     *
     * @var Stmt
     */
    protected $contextBefore;

    /**
     * Property before changes.
     *
     * @var Property
     */
    protected $propertyBefore;

    /**
     * @param string $fileBefore
     * @param string $target
     */
    public function __construct($fileBefore, $target)
    {
        $this->fileBefore = $fileBefore;
        $this->target = $target;
    }

    /**
     * Returns file path before changes.
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->fileBefore;
    }

    /**
     * Returns line position of existed property.
     *
     * @return int
     */
    public function getLine()
    {
        return 0;
    }
    /**
     * Get level.
     *
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level;
    }
}
