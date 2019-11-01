<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionCheckr\Operation;

use PHPSemVerChecker\Operation\Operation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * Class WhiteListReduced generated when schema whitelist is reduced
 */
class WhiteListReduced extends Operation
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
    protected $reason = 'Module db schema whitelist reduced (%s).';

    /**
     * File location
     *
     * @var string
     */
    private $location;

    /**
     * @param string $location
     * @param $target
     */
    public function __construct($location, $target)
    {
        $this->location = $location;
        $this->target = $target;
    }

    /**
     * Returns file path before changes
     *
     * @return string
     */
    public function getLocation() : string
    {
        return $this->location;
    }

    /**
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Returns line position of existed property
     *
     * @return int
     */
    public function getLine() : int
    {
        return 0;
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return sprintf($this->reason, $this->getTarget());
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
