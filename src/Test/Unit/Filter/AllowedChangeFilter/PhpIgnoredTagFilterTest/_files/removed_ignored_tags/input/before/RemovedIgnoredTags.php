<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tools\SemanticVersionChecker\Test\Unit\Filter\AllowedChangeFilter\PhpIgnoredTagFilterTest;

class RemovedIgnoredTags
{
    /**
     * This tag is one that ignored
     * After removing the tag, the files are still considered to match and should be filtered
     * @ignored
     */
    public function foo()
    {
        return;
    }
}
