<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Operation;

use PhpParser\Node\Stmt\Class_ as BaseClass;
use PhpParser\Node\Stmt\Interface_ as BaseInterface;
use PhpParser\Node\Stmt\Trait_ as BaseTrait;
use PhpParser\Node\Stmt\ClassLike;
use PHPSemVerChecker\Node\Statement\Class_ as Class_Statement;
use PHPSemVerChecker\Node\Statement\Interface_ as Interface_Statement;
use PHPSemVerChecker\Node\Statement\Trait_ as Trait_Statement;
use PHPSemVerChecker\Operation\Operation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * When @api annotation has been added/removed
 */
class ClassLikeApiAnnotationOperation extends Operation
{
    /**
     * Change level.
     *
     * @var int
     */
    protected $level = Level::MINOR;

    /**
     * @var ClassLike
     */
    protected $classLike;

    /**
     * @param ClassLike $classLike
     * @param string $target
     */
    public function __construct(ClassLike $classLike, $target)
    {
        $this->target = $target;
        $this->classLike = $classLike;
    }

    /**
     * @inheritDoc
     */
    public function getTarget(): string
    {
        $result = '';

        if ($this->classLike instanceof BaseClass) {
            $result = Class_Statement::getFullyQualifiedName($this->classLike);
        } elseif ($this->classLike instanceof BaseInterface) {
            $result = Interface_Statement::getFullyQualifiedName($this->classLike);
        } elseif ($this->classLike instanceof BaseTrait) {
            $result = Trait_Statement::getFullyQualifiedName($this->classLike);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getLocation(): string
    {
        return $this->target;
    }

    /**
     * @inheritDoc
     */
    public function getLine(): int
    {
        return 0;
    }

    /**
     * Get level.
     *
     * @inheritDoc
     */
    public function getLevel(): int
    {
        return $this->level;
    }
}
