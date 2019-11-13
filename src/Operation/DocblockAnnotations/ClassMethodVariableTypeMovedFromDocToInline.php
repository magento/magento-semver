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

class ClassMethodVariableTypeMovedFromDocToInline extends ClassMethodOperationUnary
{
    /**
     * @var array
     */
    protected $code = [
        'class'     => ['M146', 'M160', 'M172'],
        'interface' => ['M147', 'M147', 'M147'],
        'trait'     => ['M148', 'M161', 'M173']
    ];

    /**
     * @var array
     */
    protected $mapping = [
        'M146' => Level::MAJOR,
        'M147' => Level::MAJOR,
        'M148' => Level::MAJOR,
        'M160' => Level::MINOR,
        'M161' => Level::MINOR,
        'M172' => Level::PATCH,
        'M173' => Level::MINOR
        ];

    /**
     * @var string
     */
    protected $reason = 'Method variable typehint was moved from doc block annotation to in-line.';

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
