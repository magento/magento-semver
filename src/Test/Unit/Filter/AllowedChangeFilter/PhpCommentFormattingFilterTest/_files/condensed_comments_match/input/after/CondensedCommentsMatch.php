<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Filter\AllowedChangeFilter\PhpCommentFormattingFilterTest;

class CondensedCommentsMatch
{
    /**
     * This class has some block comments on single lines
     */

    /** And others on multiple */

    /** Even with extra blank lines */

    /**
     *
     * In some places
     *
     *
     */

    /**
     * But since the content
     *
     * of each comment line matches
     *
     *
     */

    /**
     * This file should be filtered out
     */
}
