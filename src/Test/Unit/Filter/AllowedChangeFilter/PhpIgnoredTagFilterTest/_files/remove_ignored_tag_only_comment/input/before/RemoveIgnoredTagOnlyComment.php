<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Filter\AllowedChangeFilter\PhpIgnoredTagFilterTest;

/**
 * The function comment below has only an ignored tag
 * After removing the entire comment, the files are still considered to match and should be filtered
 */
class RemoveIgnoredTagOnlyComment
{
    /**
     * @ignored
     */
    public function foo()
    {
        return;
    }
}
