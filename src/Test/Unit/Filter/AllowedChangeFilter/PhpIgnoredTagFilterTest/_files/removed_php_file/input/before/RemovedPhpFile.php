<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tools\SemanticVersionChecker\Test\Unit\Filter\AllowedChangeFilter\PhpIgnoredTagFilterTest;

class RemovedPhpFile
{
    // This file is not present in the 'after' state
    // so the result 'before' files should still contain it
}
