<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Filter\AllowedChangeFilter\PhpIgnoredTagFilterTest;

class MultipleIgnoredTags
{
    /**
     * This tag is one that is ignored
     * After removing the tag, the files are still considered to match and should be filtered
     * @ignored
     */
    public function fooTag1()
    {
        return;
    }

    /**
     * This tag is one that is ignored
     * After adding a new instance of this tag, the files are still considered to match and should be filtered
     */
    public function barTag1()
    {
        return;
    }

    /**
     * This tag is one that is ignored
     * After removing multiple instances of the tag, the files are still considered to match and should be filtered
     * @ignored
     * @ignored
     */
    public function fooFoo()
    {
        return;
    }

    /**
     * This tag is one that is ignored
     * After adding multiple new instances of this tag, the files are still considered to match and should be filtered
     */
    public function barBar()
    {
        return;
    }

    /**
     * This is a second tag that is ignored
     * After removing the tag, the files are still considered to match and should be filtered
     * @alsoIgnored
     */
    public function fooTag2()
    {
        return;
    }

    /**
     * This is a second tag that is ignored
     * After adding a new instance of this tag, the files are still considered to match and should be filtered
     */
    public function barTag2()
    {
        return;
    }

    /**
     * Both tags are ignored
     * After adding new instances of both tags, the files are still considered to match and should be filtered
     */
    public function barTag1BarTag2()
    {
        return;
    }

    /**
     * Both tags are ignored
     * After adding a new instances one tag and removing an instance of another, the files are still considered to match and should be filtered
     *
     * @alsoIgnored
     */
    public function fooBar()
    {
        return;
    }
}
