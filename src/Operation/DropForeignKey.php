<?php

namespace Magento\SemanticVersionChecker\Operation;

use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Property;
use PHPSemVerChecker\Node\Statement\Property as PProperty;
use PHPSemVerChecker\Operation\PropertyOperation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * When drop table
 */
class DropForeignKey extends \PHPSemVerChecker\Operation\Operation
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = 'M108';

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
    protected $reason = 'Foreign key removed from declaration but it may have had business logic in onDelete statement';

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
     * Get level.
     *
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level;
    }
}
