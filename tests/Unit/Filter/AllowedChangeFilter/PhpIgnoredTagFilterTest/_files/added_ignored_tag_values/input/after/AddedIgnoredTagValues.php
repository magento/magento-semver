<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Filter\AllowedChangeFilter\PhpIgnoredTagFilterTest;

class AddedIgnoredTagValues
{
    /**
     * This tag's value is ignored
     * After adding a value to the tag, the files are considered to match and should be filtered
     * @ignoredVal After Val
     */
    public function foo()
    {
        return;
    }
}
