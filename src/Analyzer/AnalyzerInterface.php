<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Analyzer;

use PhpParser\Node\Stmt;
use PHPSemVerChecker\Registry\Registry;
use PHPSemVerChecker\Report\Report;

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
