<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Operation\DocblockAnnotations;

use PhpParser\Node\Stmt\Class_;
use PHPSemVerChecker\Operation\ClassMethodOperationUnary;
use PHPSemVerChecker\SemanticVersioning\Level;

class ClassMethodVariableTypeMovedFromInlineToDoc extends ClassMethodOperationUnary
{
    /**
     * @var array
     */
    protected $code = [
        'class'     => ['M149', 'M162', 'M174'],
        'interface' => ['M150', 'M150', 'M150'],
        'trait'     => ['M151', 'M163', 'M175']
    ];

    /**
     * @var array
     */
    protected $mapping = [
        'M149' => Level::MAJOR,
        'M150' => Level::MAJOR,
        'M151' => Level::MAJOR,
        'M162' => Level::MINOR,
        'M163' => Level::MINOR,
        'M174' => Level::PATCH,
        'M175' => Level::MINOR
    ];

    /**
     * @var string
     */
    protected $reason = 'Method variable typehint was moved from in-line to doc block annotation.';

    /**
     * Returns level of error.
     *
     * @return int
     */
    public function getLevel() : int
    {
        return $this->mapping[$this->getCode()];
    }
}
