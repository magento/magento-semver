<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// @codingStandardsIgnoreFile
namespace Magento\SemanticVersionChecker;

use Magento\Framework\App\Utility\Files;
use PHPSemVerChecker\Filter\SourceFilter;

class FileChangeDetector
{
    private $sourceBeforeDir;
    private $sourceAfterDir;

    public function __construct($sourceBeforeDir, $sourceAfterDir)
    {
        $this->sourceBeforeDir = $sourceBeforeDir;
        $this->sourceAfterDir = $sourceAfterDir;
    }

    public function getChangedFiles()
    {
        $sourceBeforeFiles = Files::getFiles([$this->sourceBeforeDir], '*', true);
        $sourceAfterFiles = Files::getFiles([$this->sourceAfterDir], '*', true);
        $identicalFilter = new SourceFilter();
        $identicalFilter->filter($sourceBeforeFiles, $sourceAfterFiles);
        return array_merge($sourceAfterFiles, $sourceBeforeFiles);
    }
}
