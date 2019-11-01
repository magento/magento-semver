<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionCheckr\Operation;

/**
 * When implements is removed from marked api class.
 */
class ClassImplementsRemove extends ClassExtendsRemove
{
    /**
     * @var string
     */
    protected $code = 'M0123';

    /**
     * @var string
     */
    protected $reason = 'Implements has been removed.';
}
