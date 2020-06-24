<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Operation\Mftf;

use PHPSemVerChecker\Operation\Operation;

/**
 * Base MftfOperation
 */
class MftfOperation extends Operation
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code;

    /**
     * Operation Severity
     * @var int
     */
    protected $level;

    /**
     * Operation message.
     *
     * @var string
     */
    protected $reason;

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
     * Returns defined severity level
     *
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }
}
