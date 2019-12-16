<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Operation;

use PHPSemVerChecker\Operation\ClassMethodOperationUnary;
use PHPSemVerChecker\SemanticVersioning\Level;

class ClassMethodReturnTypingChanged extends ClassMethodOperationUnary
{
    /**
     * @var array
     */
    protected $code = [
        'class'     => ['M120', 'M121', 'M122'],
        'interface' => ['M123'],
        'trait'     => ['M124', 'M125', 'M126']
    ];

    /**
     * @var array
     */
    private $mapping = [
        'M120' => Level::MAJOR,
        'M121' => Level::MAJOR,
        'M122' => Level::PATCH,
        'M123' => Level::MAJOR,
        'M124' => Level::MAJOR,
        'M125' => Level::MAJOR,
        'M126' => Level::MAJOR
    ];


    /**
     * @var string
     */
    protected $reason = 'Method return typing changed.';

    /**
     * Returns level of error.
     *
     * @return mixed
     */
    public function getLevel()
    {
        return $this->mapping[$this->getCode()];
    }
}
