<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

// @codingStandardsIgnoreFile
namespace Magento\SemanticVersionChecker\Finder;

/**
 * Decorate finder and allow to add db_schema files there
 */
class FinderDecorator
{
    /**
     * @var  \PHPSemVerChecker\Finder\Finder
     */
    private $basicFinder;

    /**
     * @var array
     */
    private $relevantFileTypes;
    /**
     * @var array
     */
    private $excludes;


    public function __construct(array $additionalFileNameList,  array $excludes)
    {
        $this->basicFinder = new \PHPSemVerChecker\Finder\Finder();
        $this->relevantFileTypes = $additionalFileNameList;
        $this->excludes = $excludes;
    }

    /**
     * @param string $path
     * @param array $includes
     * @param array $excludes
     * @return array
     */
    public function find($path, array $includes, array $excludes = []): array
    {
        $excludes = array_merge($this->excludes, $excludes);

        $files = $this->basicFinder->find($path, $includes, $excludes);

        $fileTmp = [];
        foreach ($this->relevantFileTypes as $fileType) {
            $fileTmp[] = $this->getSchemaRelatedFilesByName($fileType, $path, $excludes);
        }

        return array_merge($files, ...$fileTmp);
    }

    /**
     * Get files with specified name in path recursively.
     *
     * @param string $name
     * @param string $path
     * @param array $excludes
     * @return array
     */
    private function getSchemaRelatedFilesByName($name, $path, array $excludes): array
    {
        $files = [];
        $finder = new \Finder\Adapter\SymfonyFinder();
        $finder->ignoreDotFiles(true)
            ->files()
            ->name($name)
            ->in($path);

        foreach ($excludes as $exclude) {
            $finder->notPath($exclude);
        }

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
    public function findFromString($path, $includes, $excludes): array
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
