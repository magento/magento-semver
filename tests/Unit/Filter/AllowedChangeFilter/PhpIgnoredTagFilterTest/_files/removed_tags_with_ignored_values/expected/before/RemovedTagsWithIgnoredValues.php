<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Filter\AllowedChangeFilter\PhpIgnoredTagFilterTest;

class RemovedTagsWithIgnoredValues
{
    /**
     * After removing the tag, the files do not match
     * @ignoredVal
     */
    public function foo()
    {
        return;
    }
}
