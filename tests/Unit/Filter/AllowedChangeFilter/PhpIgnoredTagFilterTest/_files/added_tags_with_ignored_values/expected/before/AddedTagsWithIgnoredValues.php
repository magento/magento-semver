<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Filter\AllowedChangeFilter\PhpIgnoredTagFilterTest;

class AddedTagsWithIgnoredValues
{
    /**
     * This tag has a value that is ignored, but the tag's presence is not
     * After adding a new instance of the tag, the files are not considered to match
     */
    public function foo()
    {
        return;
    }
}
