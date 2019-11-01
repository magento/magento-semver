<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Filter\AllowedChangeFilter\PhpIgnoredTagFilterTest;

class AddedNonIgnoredTagValues
{
    /**
     * This tag's value is not ignored
     * After adding a value to the tag, the files are not considered to match
     * @nonIgnoredVal After Val
     */
    public function foo()
    {
        return;
    }
}
