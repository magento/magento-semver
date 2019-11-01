<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Filter\AllowedChangeFilter\PhpIgnoredTagFilterTest;

class CaseSensitive
{
    /**
     * This filter on this tag is case-sensitive
     * Removing the tag will only be filtered out if the case matches exactly
     * @IGNOred
     */
    public function foo()
    {
        return;
    }

    /**
     * This filter on this tag is case-sensitive
     * Adding the tag will only be filtered out if the case matches exactly
     */
    public function bar()
    {
        return;
    }
}
