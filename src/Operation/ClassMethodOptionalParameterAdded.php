<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionCheckr\Operation;

use PHPSemVerChecker\Operation\ClassMethodParameterAdded;
use PHPSemVerChecker\SemanticVersioning\Level;
use PHPSemVerChecker\Operation\Visibility;

class ClassMethodOptionalParameterAdded extends ClassMethodParameterAdded
{
    /**
     * @var array
     */
    protected $code = [
        'class'     => ['M102', 'M102', 'M102'],
        'interface' => ['M102'],
        'trait'     => ['M102', 'M102', 'M102'],
    ];

    /**
     * @var string
     */
    protected $reason = 'Added optional parameter(s).';

    /**
     * @var array
     */
    protected $level = [
        'class'     => [
            Level::MINOR,
            Level::MINOR,
            Level::PATCH
        ],
        'interface' => [
            Level::MAJOR
        ],
        'trait'     => [
            Level::MINOR,
            Level::MINOR,
            Level::MINOR
        ],
    ];

    /**
     * Get level.
     *
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level[$this->context][Visibility::get($this->visibility)];
    }
}
