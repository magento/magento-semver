<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tools\SemanticVersionChecker\Analyzer;

use Magento\Tools\SemanticVersionChecker\Operation\InterfaceExtendsAdded;
use Magento\Tools\SemanticVersionChecker\Operation\InterfaceExtendsRemove;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Interface_;
use PHPSemVerChecker\Report\Report;

/**
 * Interface Extends analyzer performs comparison of interfaces and creates reports such as:
 * <ul>
 *   <li><kbd>added</kbd>: A parent has been added to the interface</li>
 *   <li><kbd>remove</kbd>: The interface extends has been removed</li>
 * <ul>
 */
class InterfaceExtendsAnalyzer extends AbstractCodeAnalyzer
{
    /**
     * Get the name of a Interface_ node
     *
     * @param Interface_ $node
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
        return Interface_::class;
    }

    /**
     * @inheritDoc
     */
    protected function reportAddedNode($report, $fileAfter, $contextAfter, $nodeAfter)
    {
        //NOP: This is not necessary for our context will implemented in Ticket MC-18245
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
     * @param Interface_ $contextBefore
     * @param Interface_ $contextAfter
     * @param string[] $toVerify
     *
     * @return Report
     */
    protected function reportChanged($report, $contextBefore, $contextAfter, $toVerify)
    {

        $namesBefore = [];
        $namesAfter = [];

        /**
         * @var Name $extend
         */
        foreach ($contextBefore->extends as $extend) {
            $namesBefore[] = $extend->toString();
        }

        /**
         * @var Name $extend
         */
        foreach ($contextAfter->extends as $extend) {
            $namesAfter[] = $extend->toString();
        }

        if (array_diff($namesAfter, $namesBefore) === true) {
            return $report;
        }

        foreach (array_diff($namesAfter, $namesBefore) as $interfaceAdded) {
            $operation = new InterfaceExtendsAdded($contextAfter, $this->fileAfter);
            $report->add($this->context, $operation);
        }

        if (count($contextBefore->extends) === 0) {
            return $report;
        }

        foreach (array_diff($namesBefore, $namesAfter) as $interfaceRemoved) {
            $operation = new InterfaceExtendsRemove($contextAfter, $this->fileAfter);
            $report->add($this->context, $operation);
        }
    }
}
