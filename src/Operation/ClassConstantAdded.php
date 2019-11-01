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

class ClassConstantAdded extends ClassConstantOperation
{
    /**
     * @var array
     */
    protected $code = [
        'class'     => 'M071',
        'interface' => 'M072',
    ];

    /**
     * @var array
     */
    protected $level = [
        'class'     => Level::MINOR,
        'interface' => Level::MINOR,
    ];

    /**
     * @var string
     */
    protected $reason = 'Constant has been added.';

    /**
     * @var string
     */
    protected $fileAfter;

    /**
     * @var \PhpParser\Node\Stmt\ClassConst
     */
    protected $constantAfter;

    /**
     * @var \PhpParser\Node\Stmt
     */
    protected $contextAfter;

    /**
     * @param string                            $context
     * @param string                            $fileAfter
     * @param \PhpParser\Node\Stmt\ClassConst   $constantAfter
     * @param \PhpParser\Node\Stmt              $contextAfter
     */
    public function __construct($context, $fileAfter, ClassConst $constantAfter, Stmt $contextAfter)
    {
        $this->fileAfter = $fileAfter;
        $this->constantAfter = $constantAfter;
        $this->contextAfter = $contextAfter;
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->fileAfter;
    }

    /**
     * @return int
     */
    public function getLine()
    {
        return $this->constantAfter->getLine();
    }

    /**
     * @return string
     */
    public function getTarget()
    {
        return ClassConstant::getFullyQualifiedName($this->contextAfter, $this->constantAfter);
    }
}
