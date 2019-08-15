<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker;

use Magento\SemanticVersionChecker\ReportBuilder;
use PHPSemVerChecker\Configuration\LevelMapping;
use PHPSemVerChecker\Report\Report;
use PHPSemVerChecker\SemanticVersioning\Level;

class SemanticVersionChecker
{
    const ANNOTATION_API = '@api';

    /**
     * @var array
     */
    public $changeLevels = [
        Level::NONE  => 'none',
        Level::PATCH => 'patch',
        Level::MINOR => 'minor',
        Level::MAJOR => 'major',
    ];

    /** @var ReportBuilder */
    private $reportBuilder;

    /** @var FileChangeDetector */
    private $fileChangeDetector;

    /** @var Report */
    private $versionReport;

    /** @var string[] */
    private $changedFiles;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\SemanticVersionChecker\ReportBuilder $reportBuilder
     * @param FileChangeDetector $fileChangeDetector
     */
    public function __construct(
        ReportBuilder $reportBuilder,
        FileChangeDetector $fileChangeDetector
    ) {
        $this->reportBuilder = $reportBuilder;
        $this->fileChangeDetector = $fileChangeDetector;
    }

    /**
     * @return Report
     */
    public function loadVersionReport()
    {
        if (!$this->versionReport) {
            $this->versionReport = $this->reportBuilder->makeCompleteVersionReport();
        }
        return $this->versionReport;
    }

    /**
     * @return array|\string[]
     */
    public function loadChangedFiles()
    {
        if (!$this->changedFiles) {
            $this->changedFiles = $this->fileChangeDetector->getChangedFiles();
        }
        return $this->changedFiles;
    }

    /**
     * @return mixed
     */
    public function getVersionIncrease()
    {
        $versionReport = $this->loadVersionReport();
        $filesChanged = $this->loadChangedFiles();
        $increaseLevels = [Level::NONE];
        foreach ($versionReport as $reportItem) {
            foreach ($reportItem as $level => $changes) {
                if (!empty($changes)) {
                    $increaseLevels[] = $level;
                }
            }
        }

        $result = max($increaseLevels);
        if ($filesChanged and $result === Level::NONE) {
            $result = Level::PATCH;
        }

        return $result;
    }
}
