<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Finder;

/**
 * Decorate finder and allow to add db_schema files there
 */
class MftfFilesFinder
{
    const APP_CODE_MFTF = "/Test/Mftf";

    /**
     */
    public function __construct()
    {
        // empty constructor
    }

    /**
     * @param string $path
     * @param string[] $includes
     * @param string[] $excludes
     * @return array
     */
    public function find($path, $includes, $excludes)
    {
        $finder = new \Finder\Adapter\SymfonyFinder();
        $finder->ignoreDotFiles(true)
            ->files()
            ->in($path)
            ->name("*.xml");

        foreach ($includes as $include) {
            $finder->path($include);
        }

        foreach ($excludes as $exclude) {
            $finder->notPath($exclude);
        }

        $files = [];
        foreach ($finder as $file) {
            if (strpos($file->getRealPath(), self::APP_CODE_MFTF) !== false) {
                $files[] = $file->getRealpath();
            }
        }
        return $files;
    }

    /**
     * @param string $path
     * @param string $includes
     * @param string $excludes
     * @return array
     */
    public function findFromString($path, $includes, $excludes)
    {
        if ($includes === '*') {
            $includes = [];
        } else {
            $includes = preg_split('@(?:\s*,\s*|^\s*|\s*$)@', $includes, -1, PREG_SPLIT_NO_EMPTY);
        }

        if ($excludes === '*') {
            $excludes = [];
        } else {
            $excludes = preg_split('@(?:\s*,\s*|^\s*|\s*$)@', $excludes, -1, PREG_SPLIT_NO_EMPTY);
        }

        return $this->find($path, $includes, $excludes);
    }
}
