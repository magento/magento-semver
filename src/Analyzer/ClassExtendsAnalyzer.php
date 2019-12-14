<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Analyzer;

use Magento\SemanticVersionChecker\Operation\ClassExtendsAdded;
use Magento\SemanticVersionChecker\Operation\ClassExtendsRemove;
use PhpParser\Node\Stmt\Class_;
use PHPSemVerChecker\Report\Report;

/**
 * Class Extends analyzer performs comparison of classes and creates reports such as:
 * <ul>
 *   <li><kbd>added</kbd>: Parent has been added to th interface</li>
 *   <li><kbd>remove</kbd>: The class extends has been removed</li>
 * <ul>
 */
class ClassExtendsAnalyzer extends AbstractCodeAnalyzer
{
    /**
     * Get the name of a Class_ node
     *
     * @param Class_ $node
     * @return string
     */
    protected function getNodeName($node)
    {
        return $node->name;
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
        if ($contextBefore->extends === null && $contextAfter->extends !== null) {
            $operation = new ClassExtendsAdded($contextAfter, $this->fileAfter);
            $report->add($this->context, $operation);
        }

        if ($contextBefore->extends !== null && $contextAfter->extends === null) {
            $operation = new ClassExtendsRemove($contextAfter, $this->fileAfter);
            $report->add($this->context, $operation);
        } else {
            return $report;
        }
    }
}
