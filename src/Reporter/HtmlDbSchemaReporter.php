<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Reporter;

use Magento\SemanticVersionChecker\DbSchemaReporter;
use PHPSemVerChecker\Report\Report;
use PHPSemVerChecker\SemanticVersioning\Level;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\SemanticVersionChecker\Analyzer\EtSchemaAnalyzer;

/**
 * @package Magento\SemanticVersionChecker
 */
class HtmlDbSchemaReporter extends DbSchemaReporter
{
    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @param Report $report
     * @param InputInterface $input
     */
    public function __construct(Report $report, InputInterface $input)
    {
        $this->input = $input;

        parent::__construct($report);
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function output(OutputInterface $output)
    {
//        $suggestedChange = $this->report->getSuggestedLevel();
//        $allowedChangeLevel = $this->input->getArgument('allowed-change-level');
//
//        $output->writeln(
//            '<tr class="text-' . ($suggestedChange > $allowedChangeLevel ? 'danger' : 'success') . '">' .
//            '<td class="test-name">Suggested semantic versioning change</td><td>' .
//            Level::toString($suggestedChange) . '</td></tr>'
//        );

        $contexts = [
            'class',
            'function',
            'interface',
            'trait',
            'database',
            'layout',
            'di',
            'system',
            'xsd',
            'less',
            'mftf',
            EtSchemaAnalyzer::CONTEXT
        ];

        foreach ($contexts as $context) {
            $this->outputReport($output, $this->report, $context);
        }
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param Report $report
     * @param string $context
     */
    protected function outputReport(OutputInterface $output, Report $report, $context)
    {
        if (!$report->hasDifferences($context)) {
            return;
        }

        $allowedChangeLevel = $this->input->getArgument('allowed-change-level');

        $output->writeln(
            '<tr class="text-' . ($report->getLevelForContext($context) > $allowedChangeLevel  ? 'danger' : 'success') .
            '"><td class="test-name">' . ucfirst($context) . ' changes (' .
            Level::toString($report->getLevelForContext($context)) . ')</td>' .
            '<td>' . ($report->getLevelForContext($context) > $allowedChangeLevel ? 'Failure' : 'Success') .
            ' <button class="btn-danger collapsible">Details</button><div class="content">'
        );
        $this->outputTable($output, $report, $context);
        $output->writeln('</div></td></tr>');
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \PHPSemVerChecker\Report\Report                   $report
     * @param string                                            $context
     */
    protected function outputTable(OutputInterface $output, Report $report, $context)
    {
        $output->writeln(
            '<table class="table table-striped"><tr><th class="column10">Level</th>' .
            '<th class="column60">Target/Location</th><th class="column30">Code/Reason</th></tr>'
        );

        $allowedChangeLevel = $this->input->getArgument('allowed-change-level');

        $sourceBeforeDirArg = $this->input->getArgument('source-before');
        $sourceBeforeDir = realpath($sourceBeforeDirArg);
        $sourceAfterDirArg = $this->input->getArgument('source-after');
        $sourceAfterDir = realpath($sourceAfterDirArg);

        $rows = [];
        foreach (Level::asList('desc') as $level) {
            $reportForLevel = $report[$context][$level];
            /** @var \PHPSemVerChecker\Operation\Operation $operation */
            foreach ($reportForLevel as $operation) {
                $location = $this->getLocation($operation);
                $target = $operation->getTarget();
                $reason = $operation->getReason();
                $code = $operation->getCode();
                $levelStr = Level::toString($level);
                $key = $location . '-' . $target . '-' . $code;
                if (array_key_exists($key, $rows)) {
                    if ($level <= Level::fromString($rows[$key][0])) {
                        continue;
                    }
                }
                $rows[$key] = [$levelStr, $location, $target, $reason, $code];

                if (substr($location, 0, strlen($sourceBeforeDir)) == $sourceBeforeDir) {
                    $location = substr($location, strlen($sourceBeforeDir));
                } elseif (substr($location, 0, strlen($sourceAfterDir)) == $sourceAfterDir) {
                    $location = substr($location, strlen($sourceAfterDir));
                }
                $target = HtmlTargetDecorator::url($target, $context, $this->input);
                $output->writeln(
                    '<tr class="text-' . ($level > $allowedChangeLevel  ? 'danger' : 'success') .
                    '"><td>' . $levelStr . '</td><td>' . $target . '<br/>' . $location . '</td><td>' . $code . ' ' .
                    htmlspecialchars($reason) . '</td></tr>'
                );
            }
        }
        $output->writeln('</table>');
    }
}
