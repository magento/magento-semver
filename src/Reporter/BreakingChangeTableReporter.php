<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// @codingStandardsIgnoreFile
namespace Magento\Tools\SemanticVersionChecker\Reporter;

use PHPSemVerChecker\Report\Report;
use PHPSemVerChecker\SemanticVersioning\Level;
use Symfony\Component\Console\Output\OutputInterface;

class BreakingChangeTableReporter extends TableReporter
{
    private $breakChangeLevels = [
        Level::MAJOR,
        Level::MINOR,
    ];

    /**
     * @var Report
     */
    private $membershipReport;

    /**
     * @var string
     */
    private $targetFile;

    /**
     * BreakingChangeTableReporter constructor.
     *
     * @param Report $apiChangeReport
     * @param Report $apiMembershipReport
     * @param string $targetFile
     * @return void
     */
    public function __construct($apiChangeReport, $apiMembershipReport, $targetFile)
    {
        parent::__construct($apiChangeReport);
        $this->membershipReport = $apiMembershipReport;
        $this->targetFile = $targetFile;
    }

    /**
     * Write the API change report to the output interface as well as the API membership report if any
     *
     * @param OutputInterface $output
     * @return void
     */
    public function output(OutputInterface $output)
    {
        $contexts = [
            'class',
            'function',
            'interface',
            'trait',
            'database',
        ];

        foreach ($contexts as $context) {
            $header = static::formatSectionHeader($this->targetFile, $context, 'breaking-change');
            $this->outputChangeReport($output, $this->report, $context, $header);
        }

        foreach ($contexts as $context) {
            $header = static::formatSectionHeader($this->targetFile, $context, 'api-membership');
            $this->outputChangeReport($output, $this->membershipReport, $context, $header);
        }
    }

    /**
     * Check if the report has changes for the context and output it as an HTML table if found
     *
     * @param OutputInterface $output
     * @param Report $report
     * @param string $context
     * @param string $header
     * @return void
     */
    private function outputChangeReport(OutputInterface $output, Report $report, $context, $header)
    {
        if (!$report->hasDifferences($context)) {
            return;
        }

        $output->writeln('');
        $output->writeln($header);
        $this->outputTable($output, $report, $context);
    }

    /**
     * Format the report as an HTML table and write it to the output interface
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \PHPSemVerChecker\Report\Report $report
     * @param string $context
     * @return void
     */
    protected function outputTable(OutputInterface $output, Report $report, $context)
    {
        $table = new HtmlTableRenderer($output);
        $table->setHeaders(['What changed', 'How it changed']);
        $rows = [];
        foreach (Level::asList('desc') as $level) {
            if (!in_array($level, $this->breakChangeLevels)) {
                continue;
            }
            $reportForLevel = $report[$context][$level];
            /** @var \PHPSemVerChecker\Operation\Operation $operation */
            foreach ($reportForLevel as $operation) {
                $target = $operation->getTarget();
                $reason = $operation->getReason();
                $rows[] = [$target, $reason];
            }
        }
        $table->setRows($rows);
        $table->render();
    }

    /**
     * Generate the HTML header line for a report section
     *
     * @param string $targetFile
     * @param string $context
     * @param string $reportType
     * @return string
     */
    public static function formatSectionHeader($targetFile, $context, $reportType)
    {
        $basename =  basename($targetFile, '.' . pathinfo($targetFile, PATHINFO_EXTENSION));
        $sectionId = $basename . '-' . $context;
        $sectionLabel = ucfirst($context);
        if ($reportType == 'api-membership') {
            $sectionId = $sectionId . '-api-membership';
            $sectionLabel = $sectionLabel . ' API membership changes';
        } else {
            $sectionLabel = $sectionLabel . ' changes';
        }
        return "<h3 id=\"$sectionId\">$sectionLabel</h3>";
    }
}
