<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tools\SemanticVersionChecker\Operation;

use PHPSemVerChecker\Operation\ClassMethodOperationDelta;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * Implements an operation that is triggered when the exception of the public method of an API class or interface has
 * been subclassed (e.g. before <kbd>LocalizedException</kbd>, after <kbd>NoSuchEntityException</kbd>).
 */
class ExceptionSubclassed extends ClassMethodOperationDelta
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = [
        'class'     => ['M129'],
        'interface' => ['M130'],
    ];

    /**
     * Error levels.
     *
     * @var array
     */
    protected $level = [
        'class'     => Level::MINOR,
        'interface' => Level::MINOR,
    ];

    /**
     * Operation message.
     *
     * @var string
     */
    protected $reason = 'Exception has been subclassed.';

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
