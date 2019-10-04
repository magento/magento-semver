<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Analyzer;

use PhpParser\Node\Stmt;
use PHPSemVerChecker\Registry\Registry;
use PHPSemVerChecker\Report\Report;

/**
 * Defines an interface for analyzer.
 * Analyzer performs comparison of and creates report.
 */
interface AnalyzerInterface
{
    /**
     * Compare with a destination registry (what the new source code is like).
     *
     * @param Registry|Stmt $registryBefore
     * @param Registry|Stmt $registryAfter
     * @return Report
     */
    public function analyze($registryBefore, $registryAfter);
}
