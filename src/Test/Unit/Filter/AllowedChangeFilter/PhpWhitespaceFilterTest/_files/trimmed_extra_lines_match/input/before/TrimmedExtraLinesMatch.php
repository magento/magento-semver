<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tools\SemanticVersionChecker\Test\Unit\Filter\AllowedChangeFilter\PhpWhitespaceFilterTest;

class TrimmedExtraLinesMatch
{
    // This class changes to get extra lines in some places
    // And removed lines in others


    // This change should be filtered out
}
