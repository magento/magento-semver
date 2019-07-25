<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// @codingStandardsIgnoreFile
namespace Magento\SemanticVersionChecker\Finder;

/**
 * Decorate finder and allow to add db_schema files there
 */
class DbSchemaFinderDecorator
{
    /**
     * @var  \PHPSemVerChecker\Finder\Finder
     */
    private $basicFinder;

    /**
     */
    public function __construct()
    {
        $this->basicFinder = new \PHPSemVerChecker\Finder\Finder();
    }

    /**
     * @param string $path
     * @param array $includes
     * @param array $excludes
     * @return array
     */
    public function find($path, array $includes, array $excludes = [])
    {
        $files = $this->basicFinder->find($path, $includes, $excludes);
        $files = array_merge($files, $this->getSchemaRelatedFilesByName('db_schema.xml', $path));
        $files = array_merge($files, $this->getSchemaRelatedFilesByName('db_schema_whitelist.json', $path));

        return $files;
    }

    /**
     * Get files with specified name in path recursively.
     *
     * @param string $name
     * @param string $path
     * @return array
     */
    private function getSchemaRelatedFilesByName($name, $path)
    {
        $files = [];
        $finder = new \Finder\Adapter\SymfonyFinder();
        $finder->ignoreDotFiles(true)
            ->files()
            ->name($name)
            ->in($path);

        foreach ($finder as $file) {
            $files[] = $file->getRealpath();
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
            $includes = preg_split('@(?:\s*,\s*|^\s*|\s*$)@', $includes, NULL, PREG_SPLIT_NO_EMPTY);
        }

        if ($excludes === '*') {
            $excludes = [];
        } else {
            $excludes = preg_split('@(?:\s*,\s*|^\s*|\s*$)@', $excludes, NULL, PREG_SPLIT_NO_EMPTY);
        }

        return $this->find($path, $includes, $excludes);
    }
}
