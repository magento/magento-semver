<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Operation;

use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Property;
use PHPSemVerChecker\Node\Statement\Property as PProperty;
use PHPSemVerChecker\Operation\PropertyOperation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * Change table resource
 */
class TableChangeResource extends \PHPSemVerChecker\Operation\Operation
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
    public function getLocation()
    {
        return $this->fileBefore;
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return sprintf($this->reason, $this->resourceBefore, $this->resourceAfter);
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
