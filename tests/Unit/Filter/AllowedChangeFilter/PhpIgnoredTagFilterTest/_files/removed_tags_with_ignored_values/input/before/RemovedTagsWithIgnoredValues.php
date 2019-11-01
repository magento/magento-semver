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
     * @ignoredVal This tag is one with its value ignored but tag whether or not the tag itself is present should still be verified
     */
    public function foo()
    {
        return;
    }
}
