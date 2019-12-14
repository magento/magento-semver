<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Operation\DocblockAnnotations;

use PHPSemVerChecker\Operation\ClassMethodOperationUnary;
use PHPSemVerChecker\SemanticVersioning\Level;

class ClassMethodReturnTypeMovedFromInlineToDoc extends ClassMethodOperationUnary
{
    /**
     * @var array
     */
    protected $code = [
        'class'     => ['M137', 'M158', 'M170'],
        'interface' => ['M141', 'M141', 'M141'],
        'trait'     => ['M145', 'M159', 'M171']
    ];

    /**
     * @var array
     */
    protected $mapping = [
        'M137' => Level::MAJOR,
        'M141' => Level::MAJOR,
        'M145' => Level::MAJOR,
        'M158' => Level::MINOR,
        'M159' => Level::MINOR,
        'M170' => Level::PATCH,
        'M171' => Level::MINOR
    ];

    /**
     * @var string
     */
    protected $reason = 'Method return typehint was moved from in-line to doc block annotation.';

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
