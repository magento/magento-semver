<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionCheckr\Operation;

use PHPSemVerChecker\SemanticVersioning\Level;

class ExtendableClassConstructorOptionalParameterAdded extends ClassConstructorOptionalParameterAdded
{
    /**
     * @var array
     */
    protected $code = [
        'class' => ['M111']
    ];

    /**
     * Change level.
     *
     * @var int
     */
    protected $level = Level::MINOR;

    /**
     * @var string
     */
    protected $reason = 'Added an optional constructor parameter to extendable @api class.';
}
