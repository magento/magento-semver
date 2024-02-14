<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker;

use PHPSemVerChecker\Report\Report;

class MergedReport extends Report
{
    /**
     * Merges with the given report including any non-standard contexts
     *
     * @param Report $report
     * @return $this
     */
    public function merge(Report $report): Report
    {
        foreach ($report->differences as $context => $levels) {
            if (!key_exists($context, $this->differences)) {
                $this->differences[$context] = $levels;
            } else {
                foreach ($levels as $level => $differences) {
                    $this->differences[$context][$level] = array_merge(
                        $this->differences[$context][$level],
                        $differences
                    );
                }
            }
        }

        return $this;
    }
}
