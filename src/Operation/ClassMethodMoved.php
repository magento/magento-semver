<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionCheckr\Operation;

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
