<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Operation;

use PHPSemVerChecker\Operation\Operation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * When drop table
 */
class DropForeignKey extends Operation
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = 'M108';

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
    protected $reason = 'Foreign key removed from declaration but it may have had business logic in onDelete statement';

    /**
     * File path before changes.
     *
     * @var string
     */
    protected $fileBefore;

    /**
     * Property context before changes.
     *
     * @var \PhpParser\Node\Stmt
     */
    protected $contextBefore;

    /**
     * Property before changes.
     *
     * @var \PhpParser\Node\Stmt\Property
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
    public function getLocation(): string
    {
        return $this->fileBefore;
    }

    /**
     * Returns line position of existed property.
     *
     * @return int
     */
    public function getLine(): int
    {
        return 0;
    }
    /**
     * Get level.
     *
     * @return mixed
     */
    public function getLevel(): int
    {
        return $this->level;
    }
}
