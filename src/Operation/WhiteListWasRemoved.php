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
class WhiteListWasRemoved extends Operation
{
    /**
     * Error codes
     *
     * @var array
     */
    protected $code = 'M109';

    /**
     * Change level
     *
     * @var int
     */
    protected $level = Level::MAJOR;

    /**
     * Operation message
     *
     * @var string
     */
    protected $reason = 'Db Whitelist from module %s was removed';

    /**
     * @var string
     */
    protected $target;
    /**
     * @var string
     */
    private $location;

    /**
     * @param string $target
     * @param string $module
     */
    public function __construct($target, $module)
    {
        $this->location = $target;
        $this->target = $module;
    }

    /**
     * Returns file path before changes
     *
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @return string
     */
    public function getReason(): string
    {
        return sprintf($this->reason, $this->target);
    }

    /**
     * Returns line position of existed property
     *
     * @return int
     */
    public function getLine(): int
    {
        return 0;
    }

    /**
     * Get level
     *
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }
}
