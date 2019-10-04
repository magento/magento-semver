<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Filter\AllowedChangeFilter\PhpWhitespaceFilterTest;

class TrimmedSpacesMatch
{
    // This class changes to get extra whitespace on some lines
        // Both at the beginning and end of different lines
// This change should be filtered out
}
