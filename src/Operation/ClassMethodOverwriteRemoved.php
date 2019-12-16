<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Operation;

use PHPSemVerChecker\Operation\ClassMethodOperationUnary;
use PHPSemVerChecker\SemanticVersioning\Level;

class ClassMethodOverwriteRemoved extends ClassMethodOperationUnary
{
    /**
     * @var array
     */
    protected $code = [
        'class' => ['V029', 'V029', 'V029']
    ];

    /**
     * @var string
     */
    protected $reason = 'Method overwrite has been removed.';

    /**
     * @var array
     */
    private $mapping = [
        'V029' => Level::PATCH
    ];

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
