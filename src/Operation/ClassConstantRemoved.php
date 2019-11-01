<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionCheckr\Operation;

use Magento\SemanticVersionCheckr\Node\Statement\ClassConstant;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassConst;
use PHPSemVerChecker\SemanticVersioning\Level;

class ClassConstantRemoved extends ClassConstantOperation
{
    /**
     * @var string
     */
    protected $code = [
        'class'     => 'M073',
        'interface' => 'M074',
    ];

    /**
     * @var int
     */
    protected $level = [
        'class'     => Level::MAJOR,
        'interface' => Level::MAJOR,
    ];

    /**
     * @var string
     */
    protected $reason = 'Constant has been removed.';

    /**
     * @var string
     */
    protected $fileBefore;

    /**
     * @var \PhpParser\Node\Stmt\ClassConst
     */
    protected $constantBefore;

    /**
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
     * @return string
     */
    public function getLocation()
    {
        return $this->fileBefore;
    }

    /**
     * @return int
     */
    public function getLine()
    {
        return $this->constantBefore->getLine();
    }

    /**
     * @return string
     */
    public function getTarget()
    {
        return ClassConstant::getFullyQualifiedName($this->contextBefore, $this->constantBefore);
    }
}
