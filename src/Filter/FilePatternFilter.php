<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

// @codingStandardsIgnoreFile
namespace Magento\SemanticVersionCheckr\Filter;

class FilePatternFilter
{
    /** @var string */
    private $sourceBeforeDir;

    /** @var string */
    private $sourceAfterDir;

    /** @var array */
    private $excludePatterns;

    /** @var array */
    private $includePatterns;

    public function __construct($includePatternsFile, $excludePatternsFile, $sourceBeforeDir, $sourceAfterDir)
    {
        // Use DIRECTORY_SEPARATOR
        $excludeSource = trim(file_get_contents($excludePatternsFile));
        $excludePatterns = $excludeSource ? explode(PHP_EOL, $excludeSource) : [];

        $includeSource = trim(file_get_contents($includePatternsFile));
        $includePatterns = $includeSource ? explode(PHP_EOL, $includeSource) : ['*'];

        $this->includePatterns = $this->getFormattedPatterns($includePatterns);
        $this->excludePatterns = $this->getFormattedPatterns($excludePatterns);
        $this->sourceBeforeDir = rtrim($sourceBeforeDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->sourceAfterDir = rtrim($sourceAfterDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    private function getFormattedPatterns($patterns)
    {
        foreach ($patterns as &$path) {
            $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
            if (DIRECTORY_SEPARATOR === '\\') {
                $path = str_replace(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, $path);
            }
            $path = ltrim($path, DIRECTORY_SEPARATOR);
            //partial support of glob format - only support * to match any one subdirectory
            $path = str_replace('*', '[^' . DIRECTORY_SEPARATOR . ']*', $path);
        }
        return $patterns;
    }

    public function filter(array &$filesBefore, array &$filesAfter)
    {
        $filesBefore = $this->_filterList($filesBefore, $this->sourceBeforeDir);
        $filesAfter = $this->_filterList($filesAfter, $this->sourceAfterDir);
    }

    private function _filterList($files, $root)
    {
        $files = $this->_filterNonIncluded($files, $root);
        return $this->_filterExcluded($files, $root);
    }

    private function _filterNonIncluded($files, $root)
    {
        /**
         * Any path that does not match an include pattern is filtered out
         */
        $includeFilter = function ($filePath) use ($root) {
            foreach ($this->includePatterns as $includePattern) {
                $includePattern = '#' . $root . $includePattern . '#';
                if (preg_match($includePattern, $filePath)) {
                    return true;
                }
            }
            return false;
        };
        return array_filter($files, $includeFilter);
    }

    private function _filterExcluded($files, $root)
    {
        /**
         * Any path that matches an exclude pattern is filtered out
         */
        $excludeFilter = function ($filePath) use ($root) {
            foreach ($this->excludePatterns as $excludePattern) {
                $excludePattern = '#' . $root . $excludePattern . '#';
                if (preg_match($excludePattern, $filePath)) {
                    return false;
                }
            }
            return true;
        };
        return array_filter($files, $excludeFilter);
    }
}
