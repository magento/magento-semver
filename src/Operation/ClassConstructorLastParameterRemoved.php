<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Operation;

use PHPSemVerChecker\SemanticVersioning\Level;

class ClassConstructorLastParameterRemoved extends ClassMethodLastParameterRemoved
{
    /**
     * @var array
     */
    protected $code = [
        'class'     => ['M101']
    ];

    /**
     * @var string
     */
    protected $reason = 'Removed last constructor parameter(s).';

    /**
     * @var array
     */
    protected $level = [
        'class'     => Level::PATCH
    ];
}
