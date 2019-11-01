<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// @codingStandardsIgnoreFile
namespace Magento\SemanticVersionCheckr;

use Magento\Framework\App\Utility\Files;
use Magento\SemanticVersionCheckr\Filter\AllowedChangeFilter\ChangedFileFilterInterface;
use Magento\SemanticVersionCheckr\Filter\SourceFilter;

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
     * @return string[]
     */
    public function getChangedFiles()
    {
        $beforeDir = $this->sourceBeforeDir;
        $afterDir = $this->sourceAfterDir;
        $beforeFiles = Files::getFiles([$beforeDir], '*', true);
        $afterFiles = Files::getFiles([$afterDir], '*', true);
        $identicalFilter = new SourceFilter();
        $identicalFilter->filter($beforeFiles, $afterFiles);

        if ($afterFiles) {
            if ($this->changedFileFilters) {
                $beforeFiltered = $this->getFileContentMap($beforeFiles, $beforeDir);
                $afterFiltered = $this->getFileContentMap($afterFiles, $afterDir);
                foreach ($this->changedFileFilters as $filter) {
                    $filter->filter($beforeFiltered, $afterFiltered);
                }
                $beforeFiles = array_filter($beforeFiles, function($file) use ($beforeDir, $beforeFiltered) {
                    return key_exists($this->getRelativePath($file, $beforeDir), $beforeFiltered);
                });
                $afterFiles = array_filter($afterFiles, function($file) use ($afterDir, $afterFiltered) {
                    return key_exists($this->getRelativePath($file, $afterDir), $afterFiltered);
                });
            }
        }
        return array_merge($afterFiles, $beforeFiles);
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
