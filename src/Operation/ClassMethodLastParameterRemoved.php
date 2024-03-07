<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Operation;

use PHPSemVerChecker\Operation\ClassMethodParameterRemoved;
use PHPSemVerChecker\SemanticVersioning\Level;

class ClassMethodLastParameterRemoved extends ClassMethodParameterRemoved
{
    /**
     * @var array
     */
    protected $code = [
        'class'     => ['M100', 'M100'],
        'interface' => ['M100'],
        'trait'     => ['M100', 'M100', 'M100'],
    ];

    /**
     * @var string
     */
    protected $reason = 'Removed last method parameter(s).';

    /**
     * @var array
     */
    protected $level = [
        'class'     => Level::MINOR,
        'interface' => Level::MINOR,
        'trait'     => Level::MINOR,
    ];

    /**
     * Get level.
     *
     * @return mixed
     */
    public function getLevel(): int
    {
        return $this->level[$this->context];
    }
}
