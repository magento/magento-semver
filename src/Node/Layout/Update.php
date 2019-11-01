<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Node\Layout;

/**
 * Data Object for layout element type `<update>`
 */
class Update implements LayoutNodeInterface
{
    /**
     * @var string
     */
    private $handle;

    public function __construct(string $handle)
    {
        $this->handle = $handle;
    }

    /**
     * @return string
     */
    public function getHandle(): string
    {
        return $this->handle;
    }

    /**
     * @return string
     */
    public function getUniqueKey(): string
    {
        return $this->handle;
    }
}
