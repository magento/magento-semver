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
 * When db_schema_whitelist.json is invalid: do not have tables declared in schema
 */
class InvalidWhitelist extends Operation
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = 'M109';

    /**
     * Change level.
     *
     * @var int
     */
    protected $level = Level::MINOR;

    /**
     * Operation message.
     *
     * @var string
     */
    protected $reason = 'Whitelist do not have table %s declared in db_schema.xml';

    /**
     * File path before changes.
     *
     * @var string
     */
    protected $location;

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

    public function getReason(): string
    {
        return sprintf($this->reason, $this->target);
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
