<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Tools\SemanticVersionChecker\Operation\Layout;

/**
 * When a `<container>` is removed.
 */
class ContainerRemoved extends BlockRemoved
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = 'M221';

    /**
     * Operation message.
     *
     * @var string
     */
    protected $reason = 'Container was removed';
}
