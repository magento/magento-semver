<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Tools\SemanticVersionChecker\Operation\Visibility;

use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * Implementation of VisibilityOperation if method decreased
 */
class MethodDecreased extends MethodOperation
{
    /**
     * Change level.
     *
     * @var int
     */
    protected $level = [
        'class' => Level::MAJOR,
        'interface' => Level::MAJOR
    ];

    /**
     * Message output in report why this test fails - used from getReason()
     *
     * @var string
     */
    protected $reason = 'Method visibility has been changed to lower lever from %s to %s';
}
