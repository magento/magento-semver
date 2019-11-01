<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionCheckr;

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

    /** @var string[]|null */
    private $changedFiles;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\SemanticVersionCheckr\ReportBuilder $reportBuilder
     * @param FileChangeDetector $fileChangeDetector
     */
    public function __construct(
        ReportBuilder $reportBuilder,
        FileChangeDetector $fileChangeDetector
    ) {
        $this->reportBuilder = $reportBuilder;
        $this->fileChangeDetector = $fileChangeDetector;
        $this->changedFiles = null;
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
     * Finds all files that exist both before and after but have changed contents
     *
     * @return array|\string[]
     */
    public function loadChangedFiles()
    {
        // Check null (unloaded) specifically as an empty array of changed files is valid
        if ($this->changedFiles === null) {
            $this->changedFiles = $this->fileChangeDetector->getChangedFiles();
        }
        return $this->changedFiles;
    }

    /**
     * Gets the required version increase level when going from the before source to the after source
     *
     * @return mixed
     */
    public function getVersionIncrease()
    {
        $versionReport = $this->loadVersionReport();
        $increaseLevels = [Level::NONE];
        foreach ($versionReport as $reportItem) {
            foreach ($reportItem as $level => $changes) {
                if (!empty($changes)) {
                    $increaseLevels[] = $level;
                }
            }
        }

        $result = max($increaseLevels);
        if ($result === Level::NONE) {
            $filesChanged = $this->loadChangedFiles();
            if ($filesChanged) {
                $result = Level::PATCH;
            }
        }

        return $result;
    }
}
