<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Filter\AllowedChangeFilter\PhpIgnoredTagFilterTest;

class IgnoreCase
{
    /**
     * This tag is one that is ignored without paying attention to case sensitivity
     * After removing the tag, the files are still considered to match and should be filtered
     */
    public function foo()
    {
        return;
    }

    /**
     * This tag is one that is ignored without paying attention to case sensitivity
     * After adding the tag, the files are still considered to match and should be filtered
     * @IGNOred
     */
    public function bar()
    {
        return;
    }

    /**
     * This tag is one that is ignored without paying attention to case sensitivity
     * After changing the tag case, the files are still considered to match and should be filtered
     * @ignoRED
     */
    public function baz()
    {
        return;
    }
}
