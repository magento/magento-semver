<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Analyzer;

use Magento\SemanticVersionChecker\Operation\ClassTraitAdded;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\TraitUse;
use PHPSemVerChecker\Report\Report;

/**
 * Class trait analyzer performs comparison of classes and creates reports such as:
 * <ul>
 *   <li><kbd>added</kbd>: New trait added to class</li>
 * <ul>
 */
class ClassTraitAnalyzer extends AbstractCodeAnalyzer
{
    /**
     * Get the name of a Class_ node
     *
     * @param Class_ $node
     * @return string
     */
    protected function getNodeName($node)
    {
        return $node->name->toString();
    }

    /**
     * Use nodes of the Class_ type for this analyzer
     *
     * @return string
     */
    protected function getNodeClass()
    {
        return Class_::class;
    }

    /**
     * @inheritDoc
     */
    protected function reportAddedNode($report, $fileAfter, $contextAfter, $nodeAfter)
    {
        // currently empty no sniff implemented.
    }

    /**
     * @inheritDoc
     */
    protected function reportRemovedNode($report, $fileBefore, $contextBefore, $nodeBefore)
    {
    }

    /**
     * @param Report $report
     * @param Class_ $contextBefore
     * @param Class_ $contextAfter
     * @param string[] $toVerify
     *
     * @return Report
     */
    protected function reportChanged($report, $contextBefore, $contextAfter, $toVerify)
    {
        $traitsBefore = [];
        $traitsAfter = [];

        /**
         * @var Name $trait
         */
        foreach ($this->getTraits($contextBefore) as $trait) {
            $traitsBefore[] = $trait->toString();
        }

        /**
         * @var Name $trait
         */
        foreach ($this->getTraits($contextAfter) as $trait) {
            $traitsAfter[] = $trait->toString();
        }

        if (array_diff($traitsAfter, $traitsBefore) === true) {
            return $report;
        }

        foreach (array_diff($traitsAfter, $traitsBefore) as $traitAdded) {
            $operation = new ClassTraitAdded($contextAfter, $this->fileAfter);
            $report->add($this->context, $operation);
        }

        foreach (array_diff($traitsBefore, $traitsAfter) as $traitRemoved) {
            // currently empty no sniff implemented.
            return $report;
        }
    }

    /**
     * @param Class_ $class
     *
     * @return array
     */
    private function getTraits(Class_ $class)
    {
        $traitsOfClass = [];

        $stmts = $class->stmts;
        if (empty($stmts)) {
            return $traitsOfClass;
        }
        $traitUses = array_filter($stmts, [$this, 'filterArrayForTraits']);

        foreach ($traitUses as $traitUse) {
            $traits = $traitUse->traits;
            foreach ($traits as $trait) {
                $traitsOfClass[] = $trait;
            }
        }

        return $traitsOfClass;
    }

    /**
     * @param Stmt $element
     * @return bool
     */
    private function filterArrayForTraits(Stmt $element)
    {
        return $element instanceof TraitUse;
    }
}
