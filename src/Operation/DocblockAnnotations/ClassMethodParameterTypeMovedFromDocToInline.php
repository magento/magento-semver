<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Operation\DocblockAnnotations;

use PHPSemVerChecker\Operation\ClassMethodOperationUnary;
use PHPSemVerChecker\SemanticVersioning\Level;

class ClassMethodParameterTypeMovedFromDocToInline extends ClassMethodOperationUnary
{
    /**
     * @var array
     */
    protected $code = [
        'class'     => ['M134', 'M152', 'M164'],
        'interface' => ['M138', 'M138', 'M138'],
        'trait'     => ['M142', 'M153', 'M165']
    ];

    /**
     * @var array
     */
    protected $mapping = [
        'M134' => Level::MAJOR,
        'M138' => Level::MAJOR,
        'M142' => Level::MAJOR,
        'M152' => Level::MINOR,
        'M153' => Level::MINOR,
        'M164' => Level::PATCH,
        'M165' => Level::MINOR
        ];

    /**
     * @var string
     */
    protected $reason = 'Method parameter typehint was moved from doc block annotation to in-line.';

    /**
     * Returns level of error.
     *
     * @return int
     */
    public function getLevel(): int
    {
        return $this->mapping[$this->getCode()];
    }
}
