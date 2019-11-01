<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Filter\AllowedChangeFilter\PhpIgnoredTagFilterTest;

class AddedNonIgnoredTags
{
    /**
     * This tag is not ignored
     * After adding a new instance of the tag, the files are not considered to match
     * @nonIgnoredTag
     */
    public function foo()
    {
        return;
    }
}
