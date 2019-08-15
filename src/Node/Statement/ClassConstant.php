<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Node\Statement;

use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassConst as BaseClassConstant;

class ClassConstant extends BaseClassConstant
{
    /**
     * Get Fully Qualified Name.
     *
     * @param Stmt $context
     * @param BaseClassConstant $constant
     * @return string
     */
    public static function getFullyQualifiedName(Stmt $context, BaseClassConstant $constant)
    {
        $fqcn = $context->name;
        if ($context->namespacedName) {
            $fqcn = $context->namespacedName->toString();
        }
        return $fqcn . '::' . $constant->consts[0]->name;
    }
}
