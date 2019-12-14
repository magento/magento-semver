<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Operation;

use PHPSemVerChecker\Operation\ClassMethodOperationDelta;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * Implements an operation that is triggered when an additional, superclassed exception is added to a public method of
 * an API class or interface (e.g. before <kbd>NoSuchEntityException</kbd>, after <kbd>NoSuchEntityException,
 * LocalizedException</kbd>).
 */
class ExceptionSuperclassAdded extends ClassMethodOperationDelta
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = [
        'class'     => ['M131'],
        'interface' => ['M132'],
    ];

    /**
     * Error levels.
     *
     * @var array
     */
    protected $level = [
        'class'     => Level::MAJOR,
        'interface' => Level::MAJOR,
    ];

    /**
     * Operation message.
     *
     * @var string
     */
    protected $reason = 'Superclassed Exception has been added.';

    /**
     * Returns level of error.
     *
     * @return int
     */
    public function getLevel()
    {
        return $this->level[$this->context];
    }
}
