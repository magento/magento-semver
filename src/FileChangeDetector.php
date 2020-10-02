<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// @codingStandardsIgnoreFile
namespace Magento\SemanticVersionChecker;

//use Magento\Framework\App\Utility\Files;
use Magento\SemanticVersionChecker\Filter\AllowedChangeFilter\ChangedFileFilterInterface;
use Magento\SemanticVersionChecker\Filter\SourceFilter;

class FileChangeDetector
{
    /** @var string */
    private $sourceBeforeDir;

    /** @var string */
    private $sourceAfterDir;

    /** @var ChangedFileFilterInterface[] */
    private $changedFileFilters;

    /**
     * FileChangeDetector constructor.
     *
     * Can supply additional filters to ignore files that only differ due to specific kinds of changes
     *
     * @param string $sourceBeforeDir
     * @param string $sourceAfterDir
     * @param ChangedFileFilterInterface[] $changedFileFilters
     */
    public function __construct($sourceBeforeDir, $sourceAfterDir, $changedFileFilters = [])
    {
        $this->sourceBeforeDir = $sourceBeforeDir;
        $this->sourceAfterDir = $sourceAfterDir;
        $this->changedFileFilters = $changedFileFilters;
    }

    /**
     * Get the set of files that were added, removed, or changed between before and after source
     *
     * @param bool $mftf
     * @return string[]
     */
    public function getChangedFiles($mftf = false)
    {
        $beforeDir = $this->sourceBeforeDir;
        $afterDir = $this->sourceAfterDir;
        $beforeFiles = $this->getFiles([$beforeDir], '*', true);
        $afterFiles = $this->getFiles([$afterDir], '*', true);
        $identicalFilter = new SourceFilter();
        $identicalFilter->filter($beforeFiles, $afterFiles);

        if ($afterFiles) {
            if ($this->changedFileFilters) {
                $beforeFiltered = $this->getFileContentMap($beforeFiles, $beforeDir);
                $afterFiltered = $this->getFileContentMap($afterFiles, $afterDir);
                foreach ($this->changedFileFilters as $filter) {
                    $filter->filter($beforeFiltered, $afterFiltered);
                }
                $beforeFiles = array_filter($beforeFiles, function ($file) use ($beforeDir, $beforeFiltered) {
                    return key_exists($this->getRelativePath($file, $beforeDir), $beforeFiltered);
                });
                $afterFiles = array_filter($afterFiles, function ($file) use ($afterDir, $afterFiltered) {
                    return key_exists($this->getRelativePath($file, $afterDir), $afterFiltered);
                });
            }
        }
        $changedFiles = array_merge($afterFiles, $beforeFiles);
        $mftfFiles = array_filter($changedFiles, function ($var) {
            return (stripos($var, '/Test/Mftf/') !== false);
        });
        if ($mftf) {
            return $mftfFiles;
        }
        return array_diff($changedFiles, $mftfFiles);
    }

    /**
     * Retrieve all files in folders and sub-folders that match pattern (glob syntax)
     *
     * @param array $dirPatterns
     * @param string $fileNamePattern
     * @param bool $recursive
     * @return array
     */
    public function getFiles(array $dirPatterns, $fileNamePattern, $recursive = true)
    {
        $result = [];
        foreach ($dirPatterns as $oneDirPattern) {
            $oneDirPattern = str_replace('\\', '/', $oneDirPattern);
            $entriesInDir = Glob::glob("{$oneDirPattern}/{$fileNamePattern}", Glob::GLOB_NOSORT | Glob::GLOB_BRACE);
            $subDirs = Glob::glob("{$oneDirPattern}/*", Glob::GLOB_ONLYDIR | Glob::GLOB_NOSORT | Glob::GLOB_BRACE);
            $filesInDir = array_diff($entriesInDir, $subDirs);
            if ($recursive) {
                $filesInSubDir = self::getFiles($subDirs, $fileNamePattern);
                $result = array_merge($result, $filesInDir, $filesInSubDir);
            }
        }
        return $result;
    }

    /**
     * Construct an in-memory map of <relative_file_path> => [<lines_of_file>]
     *
     * @param string[] $fileList
     * @param string $dir
     * @return array[]
     */
    private function getFileContentMap($fileList, $dir)
    {
        $fileMap = [];
        foreach ($fileList as $file) {
            $relativePath = $this->getRelativePath($file, $dir);
            $fileMap[$relativePath] = file($file, FILE_IGNORE_NEW_LINES);
        }
        return $fileMap;
    }

    /**
     * Helper function to get the relative directory to a file from the base directory
     *
     * @param string $file
     * @param string $dir
     * @return string
     */
    private function getRelativePath($file, $dir)
    {
        return substr($file, strlen($dir) + 1);
    }
}
