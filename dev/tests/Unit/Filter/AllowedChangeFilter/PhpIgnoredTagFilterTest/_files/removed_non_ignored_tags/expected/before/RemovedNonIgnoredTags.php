<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Filter\AllowedChangeFilter\PhpIgnoredTagFilterTest;

class RemovedNonIgnoredTags
{
    /**
     * This tag is one that is not ignored and thus needs to be compared
     * After removing the tag, the files do not match
     * @nonignored
     */
    public function foo()
    {
        return;
    }
}
