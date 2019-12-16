<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Operation;

use PHPSemVerChecker\Operation\ClassMethodOperationUnary;
use PHPSemVerChecker\SemanticVersioning\Level;

class ClassMethodParameterTypingChanged extends ClassMethodOperationUnary
{
    /**
     * @var array
     */
    protected $code = [
        'class'     => ['M113', 'M114', 'M115'],
        'interface' => ['M116'],
        'trait'     => ['M117', 'M118', 'M119']
    ];

    /**
     * @var array
     */
    private $mapping = [
        'M113' => Level::MAJOR,
        'M114' => Level::MAJOR,
        'M115' => Level::PATCH,
        'M116' => Level::MAJOR,
        'M117' => Level::MAJOR,
        'M118' => Level::MAJOR,
        'M119' => Level::MAJOR
    ];


    /**
     * @var string
     */
    protected $reason = 'Method parameter typing changed.';

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
