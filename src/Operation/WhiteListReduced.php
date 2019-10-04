<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Tools\SemanticVersionChecker\Operation;

use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * Class WhiteListReduced.
 *
 * Generated when schema whitelist is reduced.
 *
 * @package Magento\Tools\SemanticVersionChecker\Operation
 */
class WhiteListReduced extends \PHPSemVerChecker\Operation\Operation
{
    /**
     * Error code.
     *
     * @var string
     */
    protected $code = 'M110';

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
    protected $reason = 'Module db schema whitelist reduced.';

    /**
     * File location.
     *
     * @var string
     */
    private $location;

    /**
     * Operation constructor.
     *
     * @param string $location
     */
    public function __construct(string $location)
    {
        $this->location = $location;
    }

    /**
     * Returns file path before changes.
     *
     * @return string
     */
    public function getLocation() : string
    {
        return $this->location;
    }

    /**
     * Returns line position of existed property.
     *
     * @return int
     */
    public function getLine() : int
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
