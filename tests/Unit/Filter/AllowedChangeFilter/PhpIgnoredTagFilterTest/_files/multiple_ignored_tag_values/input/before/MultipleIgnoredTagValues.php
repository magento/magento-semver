<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Filter\AllowedChangeFilter\PhpIgnoredTagFilterTest;

class MultipleIgnoredTags
{
    /**
     * This tag has an ignored value
     * After removing the value, the files are still considered to match and should be filtered
     * @ignoredVal Before Val
     */
    public function fooTag1()
    {
        return;
    }

    /**
     * This tag has an ignored value
     * After adding a value to this tag, the files are still considered to match and should be filtered
     * @ignoredVal
     */
    public function barTag1()
    {
        return;
    }

    /**
     * This tag has an ignored value
     * After changing the value, the files are still considered to match and should be filtered
     * @ignoredVal Before Val
     */
    public function bazTag1()
    {
        return;
    }

    /**
     * This is a second tag that has an ignored value
     * After removing the value, the files are still considered to match and should be filtered
     * @alsoIgnoredVal Before Val
     */
    public function fooTag2()
    {
        return;
    }

    /**
     * This is a second tag that has an ignored value
     * After adding a value to this tag, the files are still considered to match and should be filtered
     * @alsoIgnoredVal
     */
    public function barTag2()
    {
        return;
    }

    /**
     * This is a second tag that has an ignored value
     * After changing the value, the files are still considered to match and should be filtered
     * @alsoIgnoredVal Before Val
     */
    public function bazTag2()
    {
        return;
    }

    /**
     * The values of both tags are ignored
     * Any combination of adding/removing/changing values still result in the files considering to match and filtering them
     *
     * @ignoredVal
     * @ignoredVal Before Val
     * @ignoredVal Another Before Val
     * @alsoIgnoredVal
     * @alsoIgnoredVal Before Val
     * @alsoIgnoredVal Another Before Val
     */
    public function fooBarBaz()
    {
        return;
    }
}
