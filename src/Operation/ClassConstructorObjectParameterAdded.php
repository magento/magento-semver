<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Operation;

use PHPSemVerChecker\Operation\ClassMethodParameterAdded;
use PHPSemVerChecker\SemanticVersioning\Level;

class ClassConstructorObjectParameterAdded extends ClassMethodParameterAdded
{
    /**
     * @var array
     */
    protected $code = [
        'class'     => ['M103']
    ];

    /**
     * @var string
     */
    protected $reason = 'Added a required constructor object parameter.';

    /**
     * @var array
     */
    protected $level = [
        'class'     => Level::MINOR
    ];

    /**
     * Get level.
     *
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level[$this->context];
    }
}