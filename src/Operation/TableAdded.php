<?php

namespace Magento\SemanticVersionCheckr\Operation;

use PhpParser\Node\Stmt;
use PHPSemVerChecker\Operation\Operation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * When added table
 */
class TableAdded extends Operation
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = 'M202';

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
    protected $reason = 'Table was added';

    /**
     * File path after changes.
     *
     * @var string
     */
    protected $location;

    /**
     * Property context after changes.
     *
     * @var Stmt
     */
    protected $contextAfter;

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
     * Returns file path after changes.
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
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
