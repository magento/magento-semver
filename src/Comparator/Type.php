<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Comparator;

use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType;

class Type extends \PHPSemVerChecker\Comparator\Type
{
    /**
     * @param Name|NullableType|string|null $type
     * @return string|null
     */
    public static function get($type)
    {
        if (! is_object($type)) {
            return $type;
        }

        if ($type instanceof NullableType) {
            return '?' . static::get($type->type);
        }

        if ($type instanceof UnionType) {
            return $type->getType();
        }

        return $type->toString();
    }
}
