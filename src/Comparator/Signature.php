<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tools\SemanticVersionChecker\Comparator;

use PHPSemVerChecker\Comparator\Type;

class Signature extends \PHPSemVerChecker\Comparator\Signature
{
    /**
     * Checks if all arguments are required
     *
     * @param array $params Array of PhpParser\Node\Param objects
     * @return bool
     */
    public static function isRequiredParams(array $params) {
        foreach ($params as $param) {
            if ($param->default !== null) {
                return false;
            }
        }
        return true;
    }

    /**
     * Checks if all parameters are optional
     *
     * @param array $params Array of PhpParser\Node\Param objects
     * @return bool
     */
    public static function isOptionalParams(array $params) {
        foreach ($params as $param) {
            if ($param->default === null) {
                return false;
            }
        }
        return true;
    }

    /**
     * Checks type hinting to determine if each parameter is non-Scalar.
     * It assumes proper PHP code style is followed, meaning only non-Scalar parameters have type hinting.
     *
     * @param array $params Array of PhpParser\Node\Param objects
     * @return bool
     */
    public static function isObjectParams(array $params) {
        foreach ($params as $param) {
            if (is_object($param->type)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Inspect method parameters for changes in count, naming, typing, or default values
     *
     * Adjusted from parent to handle parameter type changes instead of treating them as both an add and remove
     *
     * @param array $parametersA
     * @param array $parametersB
     * @return array
     */
    public static function analyze(array $parametersA, array $parametersB)
    {
        $changes = parent::analyze($parametersA, $parametersB);
        $changes = array_merge($changes, [
            'parameter_typing_added'          => false,
            'parameter_typing_removed'        => false,
            'parameter_typing_changed'        => false
        ]);
        $lengthA = count($parametersA);
        $lengthB = count($parametersB);

        $iterations = min($lengthA, $lengthB);
        for ($i = 0; $i < $iterations; ++$i) {
            // Re-implement type checking to handle type changes as a single operation instead of both add and remove
            if (Type::get($parametersA[$i]->type) !== Type::get($parametersB[$i]->type)) {
                // This section changed from parent::analyze() to handle typing changes
                if ($parametersA[$i]->type !== null && $parametersB[$i]->type !== null) {
                    $changes['parameter_typing_changed'] = true;
                } elseif ($parametersA[$i]->type !== null) {
                    $changes['parameter_typing_removed'] = true;
                } elseif ($parametersB[$i]->type !== null) {
                    $changes['parameter_typing_added'] = true;
                }
            }
        }

        return $changes;
    }
}
