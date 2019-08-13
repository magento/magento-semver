<?php

namespace Magento\SemanticVersionChecker\Operation;

use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PHPSemVerChecker\Node\Statement\ClassMethod as PClassMethod;
use PHPSemVerChecker\Operation\ClassMethodOperationUnary;
use PHPSemVerChecker\SemanticVersioning\Level;

class ClassMethodMoved extends ClassMethodOperationUnary
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = [
        'class'     => ['M091', 'M095'],
        'interface' => ['M092', 'M096'],
    ];

    /**
     * Error levels.
     *
     * @var array
     */
    protected $level = [
        'class'     => Level::PATCH,
        'interface' => Level::PATCH
    ];

    /**
     * Operation message.
     *
     * @var string
     */
    protected $reason = 'Method has been moved to parent class.';

    /**
     * Returns level of error.
     *
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level[$this->context];
    }
}
