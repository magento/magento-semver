<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Operation;

use PHPSemVerChecker\Operation\ClassMethodOperationUnary;
use PHPSemVerChecker\SemanticVersioning\Level;

class ClassMethodOverwriteAdded extends ClassMethodOperationUnary
{
    /**
     * @var array
     */
    protected $code = [
        'class' => ['V028', 'V028', 'V028']
    ];

    /**
     * @var string
     */
    protected $reason = 'Method overwrite has been added.';

    /**
     * @var array
     */
    private $mapping = [
        'V028' => Level::PATCH,
    ];

    /**
     * Returns level of error.
     *
     * @return mixed
     */
    public function getLevel(): int
    {
        return $this->mapping[$this->getCode()];
    }
}
