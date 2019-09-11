<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Filter\AllowedChangeFilter\PhpIgnoredTagFilterTest;

class ChangedIgnoredTagValues
{
    /**
     * This tag has a value that is ignored
     * After changing the value, the files are still considered to match and should be filtered
     * @ignoredVal Before Val
     */
    public function foo()
    {
        return;
    }
}
