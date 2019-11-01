<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionCheckr\Operation;

use PHPSemVerChecker\Operation\ClassMethodOperationDelta;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * Implements an operation that is triggered when the exception of the public method of an API class or interface has
 * been superclassed (e.g. before <kbd>NoSuchEntityException</kbd>, after <kbd>LocalizedException</kbd>).
 */
class ExceptionSuperclassed extends ClassMethodOperationDelta
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = [
        'class'     => ['M127'],
        'interface' => ['M128'],
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
    protected $reason = 'Exception has been superclassed.';

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
