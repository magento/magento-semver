<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Analyzer;

use Magento\SemanticVersionChecker\ClassHierarchy\DependencyGraph;
use Magento\SemanticVersionChecker\Helper\Node;
use Magento\SemanticVersionChecker\Operation\ClassLikeApiAnnotationAdded;
use Magento\SemanticVersionChecker\Operation\ClassLikeApiAnnotationRemoved;
use PhpParser\Node\Stmt\ClassLike;
use PHPSemVerChecker\Report\Report;

/**
 * Class Extends analyzer performs comparison of classes/interfaces/traits and creates reports such as:
 * <ul>
 *   <li><kbd>added</kbd>: @api annotation has been added</li>
 *   <li><kbd>remove</kbd>: @api annotation has been removed</li>
 * <ul>
 */
class ClassLikeApiAnnotationAnalyzer extends AbstractCodeAnalyzer
{
    /**
     * @param null $context
     * @param null $fileBefore
     * @param null $fileAfter
     * @param DependencyGraph|null $dependencyGraph
     */
    public function __construct(
        $context = null,
        $fileBefore = null,
        $fileAfter = null,
        DependencyGraph $dependencyGraph = null
    ) {
        parent::__construct($context, $fileBefore, $fileAfter, $dependencyGraph);
        $this->nodeHelper = new Node();
    }

    /**
     * @var Node
     */
    private $nodeHelper;

    /**
     * Get the name of a ClassLike node
     *
     * @param ClassLike $node
     * @return string
     */
    protected function getNodeName($node)
    {
        return $node->name->toString();
    }

    /**
     * Use nodes of the ClassLike type for this analyzer
     *
     * @return string
     */
    protected function getNodeClass()
    {
        return ClassLike::class;
    }

    /**
     * @inheritDoc
     */
    protected function reportAddedNode($report, $fileAfter, $contextAfter, $nodeAfter)
    {
        // implementation not needed
    }

    /**
     * @inheritDoc
     */
    protected function reportRemovedNode($report, $fileBefore, $contextBefore, $nodeBefore)
    {
        // implementation not needed
    }

    /**
     * Find changes to class/interface @api annotation
     *
     * @param Report $report
     * @param ClassLike $contextBefore
     * @param ClassLike $contextAfter
     * @param string[] $toVerify
     * @return void
     */
    protected function reportChanged($report, $contextBefore, $contextAfter, $toVerify)
    {
        $isApiBefore = $this->nodeHelper->isApiNode($contextBefore);
        $isApiAfter = $this->nodeHelper->isApiNode($contextAfter);

        if (!$isApiBefore && $isApiAfter) {
            $operation = new ClassLikeApiAnnotationAdded($contextAfter, $this->fileAfter);
            $report->add($this->context, $operation);
        } elseif ($isApiBefore && !$isApiAfter) {
            $operation = new ClassLikeApiAnnotationRemoved($contextAfter, $this->fileAfter);
            $report->add($this->context, $operation);
        }
    }
}
