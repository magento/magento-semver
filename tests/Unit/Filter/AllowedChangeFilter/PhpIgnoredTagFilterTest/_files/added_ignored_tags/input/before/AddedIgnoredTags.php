<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Filter\AllowedChangeFilter\PhpIgnoredTagFilterTest;

class AddedIgnoredTags
{
    /**
     * This tag is ignored
     * After adding a new instance of the tag, the files are considered to match and should be filtered
     */
    public function foo()
    {
        return;
    }
}
