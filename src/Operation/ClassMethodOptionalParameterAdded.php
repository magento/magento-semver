<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SemanticVersionChecker\Operation;

use PHPSemVerChecker\Operation\ClassMethodParameterAdded;
use PHPSemVerChecker\SemanticVersioning\Level;

class ClassMethodOptionalParameterAdded extends ClassMethodParameterAdded
{
    /**
     * @var array
     */
    protected $code = [
        'class'     => ['M102', 'M102'],
        'interface' => ['M102']
    ];

    /**
     * @var string
     */
    protected $reason = 'Added optional parameter(s).';

    /**
     * @var array
     */
    protected $level = [
        'class'     => Level::MINOR,
        'interface' => Level::MINOR,
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
