<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Filter\AllowedChangeFilter\PhpWhitespaceFilterTest;

class AddedPhpFile
{
    // This file was added to the 'after' state and does not exist in the 'before' state
    // so the result 'after' files should still contain it
}
