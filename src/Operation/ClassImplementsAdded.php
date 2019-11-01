<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionCheckr\Operation;

/**
 * When interface was added to marked api class.
 */
class ClassImplementsAdded extends ClassExtendsAdded
{
    /**
     * @var string
     */
    protected $code = 'M0125';

    /**
     * @var string
     */
    protected $reason = 'Interface has been added.';
}
