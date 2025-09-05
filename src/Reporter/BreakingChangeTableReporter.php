<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// @codingStandardsIgnoreFile
namespace Magento\SemanticVersionChecker\Reporter;

use PHPSemVerChecker\Report\Report;
use PHPSemVerChecker\SemanticVersioning\Level;
use Symfony\Component\Console\Output\OutputInterface;

class BreakingChangeTableReporter extends TableReporter
{
    private $breakChangeLevels = [
        Level::MAJOR,
        Level::MINOR,
        Level::PATCH,
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
        $reportContexts = array_keys($this->report->getDifferences());
        $membershipContexts = array_keys($this->membershipReport->getDifferences());

        foreach ($reportContexts as $context) {
            $header = static::formatSectionHeader($this->targetFile, $context, 'breaking-change');
            $this->outputChangeReport($output, $this->report, $context, $header);
        }

        foreach ($membershipContexts as $context) {
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
        $table->setHeaders(['<strong>Change Level</strong>', '<strong>What Changed</strong>', '<strong>How It Changed</strong>']);
        $rows = [];
        foreach (Level::asList('desc') as $level) {
            if (!in_array($level, $this->breakChangeLevels)) {
                continue;
            }
            $reportForLevel = $report[$context][$level];
            /** @var \PHPSemVerChecker\Operation\Operation $operation */
            foreach ($reportForLevel as $operation) {
                // Skip private method/property changes as they shouldn't be in breaking change reports
                if ($this->isPrivateChange($operation)) {
                    continue;
                }
                
                $levelLabel = $this->getLevelLabel($level);
                $target = $operation->getTarget();
                $reason = $operation->getReason();
                $rows[] = [$levelLabel, $target, $reason];
            }
        }
        $table->setRows($rows);
        $table->render();
    }

    /**
     * Get a human-readable label for the change level
     *
     * @param int $level
     * @return string
     */
    private function getLevelLabel(int $level): string
    {
        switch ($level) {
            case Level::MAJOR:
                return '<span style="color: #d73a49; font-weight: bold;">MAJOR (Breaking)</span>';
            case Level::MINOR:
                return '<span style="color: #f6a434; font-weight: bold;">MINOR (Non-breaking)</span>';
            case Level::PATCH:
                return '<span style="color: #28a745; font-weight: bold;">PATCH</span>';
            default:
                return 'UNKNOWN';
        }
    }

    /**
     * Check if the operation represents a private method or property change
     * 
     * Private changes are filtered out as they don't affect the public API contract.
     *
     * @param \PHPSemVerChecker\Operation\Operation $operation
     * @return bool
     */
    private function isPrivateChange($operation): bool
    {
        $target = $operation->getTarget();
        $reason = $operation->getReason();
        $operationClass = get_class($operation);
        
        // For visibility operations, check if they involve private visibility
        if ($operation instanceof \Magento\SemanticVersionChecker\Operation\VisibilityOperation) {
            try {
                // Use reflection to access protected properties
                $reflection = new \ReflectionClass($operation);
                
                if ($reflection->hasProperty('memberBefore')) {
                    $memberBeforeProperty = $reflection->getProperty('memberBefore');
                    $memberBeforeProperty->setAccessible(true);
                    $memberBefore = $memberBeforeProperty->getValue($operation);
                    
                    if ($memberBefore && method_exists('\PHPSemVerChecker\Operation\Visibility', 'getForContext')) {
                        $visibilityBefore = \PHPSemVerChecker\Operation\Visibility::getForContext($memberBefore);
                        // 1 = public, 2 = protected, 3 = private
                        if ($visibilityBefore === 3) {
                            return true;
                        }
                    }
                }
                
                if ($reflection->hasProperty('memberAfter')) {
                    $memberAfterProperty = $reflection->getProperty('memberAfter');
                    $memberAfterProperty->setAccessible(true);
                    $memberAfter = $memberAfterProperty->getValue($operation);
                    
                    if ($memberAfter && method_exists('\PHPSemVerChecker\Operation\Visibility', 'getForContext')) {
                        $visibilityAfter = \PHPSemVerChecker\Operation\Visibility::getForContext($memberAfter);
                        // 1 = public, 2 = protected, 3 = private
                        if ($visibilityAfter === 3) {
                            return true;
                        }
                    }
                }
            } catch (\Exception $e) {
                // Fall back to string matching if reflection fails
            }
        }
        
        // Check if the reason explicitly mentions private visibility
        if (preg_match('/\[private\]/', $reason)) {
            return true;
        }
        
        // Check if the target or reason indicates a private method/property
        $privateIndicators = [
            'private method',
            'private property', 
            'Private method',
            'Private property',
            '::private',
            ' private ',
            'private function',
            'private static',
            'visibility has been changed to lower lever from private',
            'visibility has been changed to higher lever from private', 
            'visibility has been changed from private',
            'visibility has been changed to private',
            'Method visibility has been changed from public to private',
            'Method visibility has been changed from protected to private',
            'Property visibility has been changed from public to private',
            'Property visibility has been changed from protected to private',
        ];
        
        foreach ($privateIndicators as $indicator) {
            if (stripos($target, $indicator) !== false || stripos($reason, $indicator) !== false) {
                return true;
            }
        }
        
        // Check for visibility operations that involve private members
        if (strpos($operationClass, 'Visibility') !== false) {
            // For visibility operations, check if it involves changing from/to private
            if (stripos($reason, 'private') !== false) {
                return true;
            }
        }
        
        // Check for specific operation classes that handle private changes
        $privateOperationClasses = [
            'PrivateMethod',
            'PrivateProperty', 
            'Private',
        ];
        
        foreach ($privateOperationClasses as $privateClass) {
            if (stripos($operationClass, $privateClass) !== false) {
                return true;
            }
        }
        
        // Check if the target contains patterns that suggest private members
        // Pattern: ClassName::privateMethodName or ClassName::$privateProperty
        if (preg_match('/::([a-z_][a-zA-Z0-9_]*|\$[a-z_][a-zA-Z0-9_]*)/', $target, $matches)) {
            $memberName = $matches[1];
            // If member name starts with underscore (common private naming convention)
            if (strpos($memberName, '_') === 0) {
                return true;
            }
        }
        
        // Check for common private method patterns in the target
        if (preg_match('/::(_[a-zA-Z0-9_]+|[a-z][a-zA-Z0-9]*Private[a-zA-Z0-9]*)\(/', $target)) {
            return true;
        }
        
        return false;
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
