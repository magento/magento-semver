<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Comparator;

use PhpParser\Node\Stmt;
use PHPSemVerChecker\Operation\Visibility as VisibilityOperation;

class Visibility
{
    /**
     * Inspect method parameters for changes in count, naming, typing, or default values
     *
     * Adjusted from parent to handle parameter type changes instead of treating them as both an add and remove
     *
     * @param Stmt $propertyBefore
     * @param Stmt $propertyAfter
     *
     * @return int
     */
    public static function analyze(Stmt $propertyBefore, Stmt $propertyAfter)
    {
        $visibilityBefore = VisibilityOperation::getForContext($propertyBefore);
        $visibilityAfter  = VisibilityOperation::getForContext($propertyAfter);

        return $visibilityAfter - $visibilityBefore;
    }
}
