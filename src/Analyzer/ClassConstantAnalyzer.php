<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Analyzer;

use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PHPSemVerChecker\Report\Report;
use Magento\SemanticVersionChecker\Operation\ClassConstantAdded;
use Magento\SemanticVersionChecker\Operation\ClassConstantMoved;
use Magento\SemanticVersionChecker\Operation\ClassConstantRemoved;

/**
 * Class constant analyzer.
 * Performs comparison of changed constants and creates reports such as:
 * - class constants moved to parent
 * - class constants removed
 * - class constants added
 */
class ClassConstantAnalyzer extends AbstractCodeAnalyzer
{
    /**
     * Get the name of a ClassConst node
     *
     * @param ClassConst $constant
     * @return string
     */
    protected function getNodeName($constant)
    {
        return $constant->consts[0]->name;
    }

    /**
     * Use nodes of the Property type for this analyzer
     *
     * @return string
     */
    protected function getNodeClass()
    {
        return ClassConst::class;
    }

    /**
     * Create and report a ClassConstantAdded operation
     *
     * @param Report $report
     * @param string $fileAfter
     * @param ClassLike $classAfter
     * @param ClassConst $constantAfter
     * @return void
     */
    protected function reportAddedNode($report, $fileAfter, $classAfter, $constantAfter)
    {
        $report->add($this->context, new ClassConstantAdded($this->context, $fileAfter, $constantAfter, $classAfter));
    }

    /**
     * Create and report a ClassConstantRemoved operation
     *
     * @param Report $report
     * @param string $fileBefore
     * @param ClassLike $classBefore
     * @param ClassConst $constantBefore
     * @return void
     */
    protected function reportRemovedNode($report, $fileBefore, $classBefore, $constantBefore)
    {
        $report->add(
            $this->context,
            new ClassConstantRemoved($this->context, $fileBefore, $constantBefore, $classBefore)
        );
    }

    /**
     * Create and report a ClassConstantMoved operation
     *
     * @param Report $report
     * @param string $fileBefore
     * @param ClassLike $classBefore
     * @param ClassConst $constantBefore
     * @return void
     */
    protected function reportMovedNode($report, $fileBefore, $classBefore, $constantBefore)
    {
        $report->add(
            $this->context,
            new ClassConstantMoved($this->context, $fileBefore, $constantBefore, $classBefore)
        );
    }
}
