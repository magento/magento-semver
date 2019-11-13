<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Filter\AllowedChangeFilter\PhpCommentFormattingFilterTest;

class FilteredChangesRemain
{
    /**      This class has comments normalized    */
    /**  */
    /**             */
        /**
 *After applying the comment filter
     */
//                  But also
        //    Some content that has changed
    //
//
/**  */
    public function beforeFunction() {
        return;
    }

    /**
     *
     *
     *
     *
     */
    /**
     *The result files should have
            *Comments that match
     */

    /**
     *
     * but changes still in place
     */
}
