<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionCheckr\Console\Command;

use Magento\SemanticVersionCheckr\BreakingChangeDocReportBuilder;
use Magento\SemanticVersionCheckr\Reporter\BreakingChangeTableReporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class BackwardIncompatibleChangesCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('update-breaking-changes')
            ->setDescription('Update the file with a list of backward incompatible changes between two sources.')
            ->setDefinition([
                new InputArgument('source-before', InputArgument::REQUIRED, 'Source directory to check against'),
                new InputArgument('source-after', InputArgument::REQUIRED, 'Source directory to check'),
                new InputArgument('target-file', InputArgument::REQUIRED, 'Relative path to the devdoc file to update'),
                new InputOption(
                    'include-patterns',
                    '',
                    InputArgument::OPTIONAL,
                    'Path to a file containing include patterns',
                    realpath(__DIR__ . '/../../resources/application_includes.txt')
                ),
                new InputOption(
                    'exclude-patterns',
                    '',
                    InputArgument::OPTIONAL,
                    'Path to a file containing exclude patterns',
                    realpath(__DIR__ . '/../../resources/application_excludes.txt')
                )
            ]);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $cliOutput
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $cliOutput)
    {
        $sourceBeforeDirArg = $input->getArgument('source-before');
        $sourceBeforeDir = realpath($sourceBeforeDirArg);
        $sourceAfterDirArg = $input->getArgument('source-after');
        $sourceAfterDir = realpath($sourceAfterDirArg);
        $targetFile = $input->getArgument('target-file');

        $includePatternsPath = $input->getOption('include-patterns');
        $excludePatternsPath = $input->getOption('exclude-patterns');

        $reportBuilder = new BreakingChangeDocReportBuilder(
            $includePatternsPath,
            $excludePatternsPath,
            $sourceBeforeDir,
            $sourceAfterDir
        );

        $reportBuilder->makeCompleteVersionReport();
        $changeReport = $reportBuilder->getBreakingChangeReport();
        $membershipReport = $reportBuilder->getApiMembershipReport();

        // Log report output
        $logOutputStream = new BufferedOutput();

        // PHP analysis
        $logReporter = new BreakingChangeTableReporter($changeReport, $membershipReport, $targetFile);
        $logReporter->output($logOutputStream);
        $content = $logOutputStream->fetch();

        file_put_contents($targetFile, $content);
    }
}
