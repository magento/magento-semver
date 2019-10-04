<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Tools\SemanticVersionChecker\Node\Layout;

/**
 * Data Object Interface for layout elements.
 */
interface LayoutNodeInterface
{
    /**
     * @return string
     */
    public function getUniqueKey(): string;
}
