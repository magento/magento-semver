<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Operation;

use PhpParser\Node\Stmt\Class_ as BaseClass;
use PHPSemVerChecker\Node\Statement\Class_ as Class_Alias;
use PHPSemVerChecker\Operation\Operation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * When extend is removed from marked api class.
 */
class ClassExtendsRemove extends Operation
{
    /**
     * Change level.
     *
     * @var int
     */
    protected $level = Level::MAJOR;

    /**
     * @var string
     */
    protected $code = 'M0122';

    /**
     * @var string
     */
    protected $reason = 'Extends has been removed.';

    /**
     * @var BaseClass
     */
    private $class;

    /**
     * @param BaseClass $contextValue
     * @param string $target
     */
    public function __construct(BaseClass $class, $target)
    {
        $this->target = $target;
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getTarget()
    {
        return Class_Alias::getFullyQualifiedName($this->class);
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->target;
    }

    /**
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
