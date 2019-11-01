<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionCheckr\Operation;

use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * When db_schema_whitelist.json is invalid: do not have tables declared in schema
 */
class InvalidWhitelist extends \PHPSemVerChecker\Operation\Operation
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
    protected $fileBefore;

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

    public function getReason()
    {
        return sprintf($this->reason, $this->target);
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
