<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Scanner;

/**
 * Resolve the module name by given paths.
 */
class ModuleNamespaceResolver
{
    /**
     * Returns the Module Name by given `etc/*` file path.
     *
     * @param string $filePath
     * @return string
     */
    public function resolveByEtcDirFilePath(string $filePath): string
    {
        $matches = [];
        preg_match('/([\w]*)\/([\w]*)\/etc/', $filePath, $matches);
        return sprintf('%s_%s', $matches[1], $matches[2]);
    }

    /**
     * Returns the Module Name by given `view/*` file path.
     *
     * @param string $filePath
     * @return string
     */
    public function resolveByViewDirFilePath(string $filePath): string
    {
        $matches = [];
        preg_match('/([\w]*)\/([\w]*)\/view/', $filePath, $matches);
        return sprintf('%s_%s', $matches[1], $matches[2]);
    }
}
