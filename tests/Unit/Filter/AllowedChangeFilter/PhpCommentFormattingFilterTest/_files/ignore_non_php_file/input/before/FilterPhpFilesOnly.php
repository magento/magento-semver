<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Filter\AllowedChangeFilter\PhpCommentFormattingFilterTest;

class FilterPhpFilesOnly
{
    // This php file is identical in the before and after state and should be filtered out
    // but any non-PHP files should not be filtered out even if they are identical
}
