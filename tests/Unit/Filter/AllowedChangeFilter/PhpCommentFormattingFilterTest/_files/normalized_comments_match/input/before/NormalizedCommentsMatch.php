<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Filter\AllowedChangeFilter\PhpCommentFormattingFilterTest;

class NormalizedCommentsMatch
{
    //  This class has changes to comment formatting
    /**
     * but not changes to the content
     *
*
     */
  //     or type (// or /** */)
                            /**
     *                Of the comments
     *
                             *
     *            Including blank lines


*/
    //This change should be filtered out
}
