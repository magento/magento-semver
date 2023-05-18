<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Comparator;

use PHPSemVerChecker\Comparator\Node;

class Signature extends \PHPSemVerChecker\Comparator\Signature
{
    /**
     * Checks if all arguments are required
     *
     * @param array $params Array of PhpParser\Node\Param objects
     * @return bool
     */
    public static function isRequiredParams(array $params)
    {
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
    public static function isOptionalParams(array $params)
    {
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
    public static function isObjectParams(array $params)
    {
        foreach ($params as $param) {
            if ($param->type instanceof \PhpParser\Node\Name\FullyQualified) {
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
        // @TODO need to revert this change once new version of tomzx/php-semver-checker is released
        // After https://github.com/tomzx/php-semver-checker/issues/179 issue is addressed.
        // Moving the implementation of library to core to fix critical failure due to this library issue
        $changes = [
            'parameter_added'                 => false,
            'parameter_removed'               => false,
            'parameter_renamed'               => false,
            'parameter_typing_added'          => false,
            'parameter_typing_removed'        => false,
            'parameter_default_added'         => false,
            'parameter_default_removed'       => false,
            'parameter_default_value_changed' => false,
        ];
        $lengthA = count($parametersA);
        $lengthB = count($parametersB);

        // TODO(tom@tomrochette.com): This is only true if newer params do not have defaults
        if ($lengthA < $lengthB) {
            $changes['parameter_added'] = true;
        } elseif ($lengthA > $lengthB) {
            $changes['parameter_removed'] = true;
        }

        $iterations = min($lengthA, $lengthB);
        for ($i = 0; $i < $iterations; ++$i) {
            // Name checking
            if ($parametersA[$i]->var->name !== $parametersB[$i]->var->name) {
                $changes['parameter_renamed'] = true;
            }

            // Type checking
            if (Type::get($parametersA[$i]->type) !== Type::get($parametersB[$i]->type)) {
                if ($parametersA[$i]->type !== null) {
                    $changes['parameter_typing_removed'] = true;
                }
                if ($parametersB[$i]->type !== null) {
                    $changes['parameter_typing_added'] = true;
                }
            }

            // Default checking
            if ($parametersA[$i]->default === null && $parametersB[$i]->default === null) {
                // Do nothing
            } elseif ($parametersA[$i]->default !== null && $parametersB[$i]->default === null) {
                $changes['parameter_default_removed'] = true;
            } elseif ($parametersA[$i]->default === null && $parametersB[$i]->default !== null) {
                $changes['parameter_default_added'] = true;
                // TODO(tom@tomrochette.com): Not all nodes have a value property
            } elseif (!Node::isEqual($parametersA[$i]->default, $parametersB[$i]->default)) {
                $changes['parameter_default_value_changed'] = true;
            }
        }

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
