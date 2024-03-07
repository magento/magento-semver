<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Operation;

use Magento\SemanticVersionChecker\Node\Statement\ClassConstant;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassConst;
use PHPSemVerChecker\SemanticVersioning\Level;

class ClassConstantMoved extends ClassConstantOperation
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = [
        'class'     => 'M075',
        'interface' => 'M076',
    ];

    /**
     * Error levels.
     *
     * @var array
     */
    protected $level = [
        'class'     => Level::PATCH,
        'interface' => Level::PATCH,
    ];

    /**
     * Operation message.
     *
     * @var string
     */
    protected $reason = 'Constant has been moved to parent class or implemented interface.';

    /**
     * File path before changes.
     *
     * @var string
     */
    protected $fileBefore;

    /**
     * Constant node before changes.
     *
     * @var \PhpParser\Node\Stmt\ClassConst
     */
    protected $constantBefore;

    /**
     * Constant context before changes.
     *
     * @var \PhpParser\Node\Stmt
     */
    protected $contextBefore;

    /**
     * @param string                            $context
     * @param string                            $fileBefore
     * @param \PhpParser\Node\Stmt\ClassConst   $constantBefore
     * @param \PhpParser\Node\Stmt              $contextBefore
     *
     */
    public function __construct($context, $fileBefore, ClassConst $constantBefore, Stmt $contextBefore)
    {
        $this->fileBefore = $fileBefore;
        $this->constantBefore = $constantBefore;
        $this->contextBefore = $contextBefore;
        $this->context = $context;
    }

    /**
     * Returns file path before changes.
     *
     * @return string
     */
    public function getLocation(): string
    {
        return $this->fileBefore;
    }

    /**
     * Returns line position of existed constant.
     *
     * @return int
     */
    public function getLine(): int
    {
        return $this->constantBefore->getLine();
    }

    /**
     * Returns fully qualified name of constant.
     *
     * @return string
     */
    public function getTarget(): string
    {
        return ClassConstant::getFullyQualifiedName($this->contextBefore, $this->constantBefore);
    }
}
