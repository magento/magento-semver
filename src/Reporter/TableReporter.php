<?php

namespace Magento\SemanticVersionChecker\Reporter;

use PHPSemVerChecker\Report\Report;
use PHPSemVerChecker\Reporter\Reporter;
use PHPSemVerChecker\SemanticVersioning\Level;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

class TableReporter extends Reporter
{
    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \PHPSemVerChecker\Report\Report $report
     * @param string $context
     * @return void
     */
    protected function outputTable(OutputInterface $output, Report $report, $context)
    {
        $table = new Table($output);
        $table->setHeaders(['Level', 'Location', 'Target', 'Reason', 'Code']);
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
            }
        }
        $table->setRows($rows);
        $table->render();
    }
}
