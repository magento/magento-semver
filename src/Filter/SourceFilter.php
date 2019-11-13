<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Filter;

/**
 * Filter Implementation to remove not changed files from the file list.
 */
class SourceFilter
{
    /**
     * Filters unchanged files
     *
     * @param array $filesBefore
     * @param array $filesAfter
     *
     * @return int
     */
    public function filter(array &$filesBefore, array &$filesAfter): int
    {
        $hashedBefore = [];
        $jsonHashedBefore = [];
        foreach ($filesBefore as $fileBefore) {
            if ($this->isJson($fileBefore)) {
                $jsonHashedBefore[] = $fileBefore;
            } else {
                $hashedBefore[$this->getHash($fileBefore)] = $fileBefore;
            }
        }

        $hashedAfter = [];
        $jsonHashedAfter = [];
        foreach ($filesAfter as $fileAfter) {
            if ($this->isJson($fileAfter)) {
                $jsonHashedAfter[] = $fileAfter;
            } else {
                $hashedAfter[$this->getHash($fileAfter)] = $fileAfter;
            }
        }

        $intersection = array_intersect_key($hashedBefore, $hashedAfter);
        $filesBefore = array_merge(array_values(array_diff_key($hashedBefore, $intersection)), $jsonHashedBefore);
        $filesAfter = array_merge(array_values(array_diff_key($hashedAfter, $intersection)), $jsonHashedAfter);

        return count($intersection);
    }

    /**
     * Checks if file has JSON extension
     *
     * @param string $file
     *
     * @return bool
     */
    private function isJson(string $file): bool
    {
        return (bool)preg_match('/^.*.json/', $file);
    }

    /**
     * Returns the an sha1 file hash to filter not changed files.
     *
     * @param string $fileName
     * @return string
     */
    private function getHash(string $fileName): string
    {
        // for xml we need to cache also the parts of dirname if move a xml than it should be also marked as change.
        if (preg_match('/^.*.xml$/', $fileName)) {
            $path = basename(pathinfo($fileName, PATHINFO_DIRNAME));
            return sha1(file_get_contents($fileName).$path);
        }

        return sha1_file($fileName);
    }
}
