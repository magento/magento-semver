<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Filter\AllowedChangeFilter\PhpCommentFormattingFilterTest;

//
class TrimmedEmptyCommentsMatch
{
    // This class changes to have empty comments in some places
    /**  */

    /** And removed empty comments in others */

    // This change should be filtered out
    //
    /**
     *
     *
     */
}
