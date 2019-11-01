<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionCheckr\Operation\Visibility;

use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * Implementation of VisibilityOperation if method increased
 */
class MethodIncreased extends MethodOperation
{
    /**
     * Change level.
     *
     * @var int
     */
    protected $level = [
        'class' => Level::MINOR,
        'interface' => Level::MINOR
    ];

    /**
     * Message output in report why this test fails - used from getReason()
     *
     * @var string
     */
    protected $reason = 'Method visibility has been changed to higher lever from %s to %s';
}
