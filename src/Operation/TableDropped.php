<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Operation;

use PHPSemVerChecker\Operation\Operation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * When drop table
 */
class TableDropped extends Operation
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = 'M104';

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
    protected $reason = 'Table was dropped';

    /**
     * File path before changes.
     *
     * @var string
     */
    protected $location;

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
     * @param string $location
     * @param string $target
     */
    public function __construct($location, $target)
    {
        $this->location = $location;
        $this->target = $target;
    }

    /**
     * Returns file path before changes.
     *
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
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
