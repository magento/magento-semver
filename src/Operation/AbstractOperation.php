<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionCheckr\Operation;

use PHPSemVerChecker\Operation\Operation;

/**
 * Defines an abstract operation for analysis.
 */
abstract class AbstractOperation extends Operation
{
    /**
     * Defines the level of the operation.
     *
     * @var int
     */
    protected $level;

    /**
     * Defines the file in which the change was detected.
     *
     * @var string
     */
    private $file;

    /**
     * Constructor.
     *
     * @param string $file
     * @param string $target
     */
    public function __construct(string $file, string $target)
    {
        $this->file   = $file;
        $this->target = $target;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->file;
    }

    /**
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @return int
     */
    public function getLine()
    {
        return 0;
    }
}
