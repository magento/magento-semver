<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Analyzer;

use Magento\SemanticVersionChecker\Operation\ClassImplementsAdded;
use Magento\SemanticVersionChecker\Operation\ClassImplementsRemove;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PHPSemVerChecker\Report\Report;

/**
 * Class Implements analyzer performs comparison of classes and creates reports such as:
 * <ul>
 *   <li><kbd>added</kbd>: Interface has been added to the class</li>
 *   <li><kbd>remove</kbd>: The class implements has been removed</li>
 * <ul>
 */
class ClassImplementsAnalyzer extends AbstractCodeAnalyzer
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
        //NOP: This is not necessary for our context will implemented in Ticket MC-18245.
    }

    /**
     * @inheritDoc
     */
    protected function reportRemovedNode($report, $fileBefore, $contextBefore, $nodeBefore)
    {
        //NOP: This is not necessary for our context
    }

    /**
     * @param Report $report
     * @param Class_ $contextBefore
     * @param Class_ $contextAfter
     * @param string[] $toVerify
     *
     * @return Report|void
     */
    protected function reportChanged($report, $contextBefore, $contextAfter, $toVerify)
    {
        $namesBefore = [];
        $namesAfter = [];

        /**
         * @var Name $interfaceName
         */
        foreach ($contextBefore->implements as $interfaceName) {
            $namesBefore[] = $interfaceName->toString();
        }

        /**
         * @var Name $interfaceName
         */
        foreach ($contextAfter->implements as $interfaceName) {
            $namesAfter[] = $interfaceName->toString();
        }

        foreach (array_diff($namesBefore, $namesAfter) as $interfaceRemoved) {
            $operation = new ClassImplementsRemove($contextAfter, $this->fileAfter);
            $report->add($this->context, $operation);
        }

        foreach (array_diff($namesAfter, $namesBefore) as $interfaceAdded) {
            $operation = new ClassImplementsAdded($contextAfter, $this->fileAfter);
            $report->add($this->context, $operation);
        }
    }
}
