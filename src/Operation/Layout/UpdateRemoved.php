<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Operation\Layout;

/**
 * When a `<update>` is removed.
 */
class UpdateRemoved extends ContainerRemoved
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = 'M222';

    /**
     * Operation message.
     *
     * @var string
     */
    protected $reason = 'An Update was removed';
}
