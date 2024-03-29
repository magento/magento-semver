<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Operation;

use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Property;
use PHPSemVerChecker\Operation\Operation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * Change table resource
 */
class TableChangeResource extends Operation
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = 'M105';

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
    protected $reason = 'Table chard was changed from %s to %s';

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
     * @var string
     */
    private $resourceBefore;

    /**
     * @var string
     */
    private $resourceAfter;

    /**
     * @param string $fileBefore
     * @param string $target
     * @param string $resourceBefore
     * @param string $resourceAfter
     */
    public function __construct($fileBefore, $target, $resourceBefore, $resourceAfter)
    {
        $this->fileBefore = $fileBefore;
        $this->target = $target;
        $this->resourceBefore = $resourceBefore;
        $this->resourceAfter = $resourceAfter;
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
     * @return string
     */
    public function getReason(): string
    {
        return sprintf($this->reason, $this->resourceBefore, $this->resourceAfter);
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
