<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Filter\AllowedChangeFilter\PhpIgnoredTagFilterTest;

/**
 * The function below does not have a comment.
 * After adding a comment containing only an ignored tag, the files are still considered to match and should be filtered
 */
class AddIgnoredTagOnlyComment
{
    public function foo()
    {
        return;
    }
}
