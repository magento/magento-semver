<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Operation;

use PhpParser\Node\Stmt\Class_ as BaseClass;
use PHPSemVerChecker\Node\Statement\Class_ as Class_Statement;
use PHPSemVerChecker\Operation\Operation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * When parent was added to marked api class.
 */
class ClassExtendsAdded extends Operation
{
    /**
     * Change level.
     *
     * @var int
     */
    protected $level = Level::MINOR;

    /**
     * @var string
     */
    protected $code = 'M0124';

    /**
     * @var string
     */
    protected $reason = 'Parent has been added.';

    /**
     * @var BaseClass
     */
    private $class;

    /**
     * @param BaseClass $class
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
    public function getTarget(): string
    {
        return Class_Statement::getFullyQualifiedName($this->class);
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->target;
    }

    /**
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
