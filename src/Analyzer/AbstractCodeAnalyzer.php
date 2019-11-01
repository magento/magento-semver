<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionCheckr\Analyzer;

use Magento\SemanticVersionCheckr\Helper\ClassParser;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PHPSemVerChecker\Registry\Registry;
use PHPSemVerChecker\Report\Report;

abstract class AbstractCodeAnalyzer implements AnalyzerInterface
{
    /**
     * Analyzer context.
     *
     * @var null|string
     */
    protected $context;

    /**
     * File path before changes.
     *
     * @var null|string
     */
    protected $fileBefore;

    /**
     * File path after changes.
     *
     * @var null|string
     */
    protected $fileAfter;

    /**
     * @param string $context
     * @param string $fileBefore
     * @param string $fileAfter
     */
    public function __construct($context = null, $fileBefore = null, $fileAfter = null)
    {
        $this->context = $context;
        $this->fileBefore = $fileBefore;
        $this->fileAfter = $fileAfter;
    }

    public function analyze($registryBefore, $registryAfter)
    {
        $report = new Report();

        $beforeNameMap = $this->getNodeNameMap($registryBefore);
        $afterNameMap = $this->getNodeNameMap($registryAfter);

        $namesBefore = array_keys($beforeNameMap);
        $namesAfter = array_keys($afterNameMap);
        $added = array_diff($namesAfter, $namesBefore);
        $removed = array_diff($namesBefore, $namesAfter);
        $toVerify = array_intersect($namesBefore, $namesAfter);

        $this->reportAdded($report, $registryAfter, $added);
        $this->reportMovedOrRemoved($report, $registryBefore, $registryAfter, $removed);

        $this->reportChanged(
            $report,
            $registryBefore,
            $registryAfter,
            $toVerify
        );

        return $report;
    }

    /**
     * Gets the appropriate nodes from the context and maps them to their names
     *
     * @param Node|Registry $context
     * @return Node[]
     */
    protected function getNodeNameMap($context)
    {
        $entities = ClassParser::filterNodes($context, $this->getNodeClass());
        $keyed = [];
        foreach ($entities as $entity) {
            $keyed[$this->getNodeName($entity)] = $entity;
        }
        return $keyed;
    }

    /**
     * Has the node been moved to parent class.
     *
     * @param ClassParser $parsedClass
     * @param Node $removedNode
     * @return bool
     */
    protected function isMovedToParent($parsedClass, $removedNode)
    {
        $parentClass = $parsedClass->getParentClass();

        if ($removedNode instanceof ClassLike ||
            $parentClass === null ||
            (property_exists($removedNode, 'flags') && in_array($removedNode->flags, [Class_::MODIFIER_PRIVATE]))) {
            return false;
        }

        $parentNodes = $parentClass->getNodesOfType($this->getNodeClass());
        foreach ($parentNodes as $parentNode) {
            $parentNodeName = $this->getNodeName($parentNode);
            $removedNodeName = $this->getNodeName($removedNode);
            if ($parentNodeName === $removedNodeName &&
                (!property_exists($parentNode, 'flags') || $parentNode->flags === $removedNode->flags)) {
                return true;
            }
        }

        return $this->isMovedToParent($parentClass, $removedNode);
    }

    /**
     * Get the name for a given node of the type analyzed
     *
     * @param Node $node
     * @return string
     */
    abstract protected function getNodeName($node);

    /**
     * The class of the nodes to analyze
     *
     * @return string
     */
    abstract protected function getNodeClass();

    /**
     * Create and report a NodeAdded operation
     *
     * @param Report $report
     * @param string $fileAfter
     * @param Registry|Node $contextAfter
     * @param Node $nodeAfter
     * @return void
     */
    abstract protected function reportAddedNode($report, $fileAfter, $contextAfter, $nodeAfter);

    /**
     * Create and report a NodeRemoved operation
     *
     * @param Report $report
     * @param string $fileBefore
     * @param Registry|Node $contextBefore
     * @param Node $nodeBefore
     * @return void
     */
    abstract protected function reportRemovedNode($report, $fileBefore, $contextBefore, $nodeBefore);

    /**
     * Create and report a NodeMoved operation
     *
     * @param Report $report
     * @param string $fileBefore
     * @param Registry|Node $contextBefore
     * @param Node $nodeBefore
     * @return void
     */
    protected function reportMovedNode($report, $fileBefore, $contextBefore, $nodeBefore)
    {
        // ClassLike nodes do not have Moved operations, so do not enforce implementing this method
    }

    /**
     * Report the list of added nodes
     *
     * @param Report $report
     * @param Node|Registry $contextAfter
     * @param string[] $addedNames
     * @return void
     */
    protected function reportAdded($report, $contextAfter, $addedNames)
    {
        /** @var Node[] $afterNameMap */
        $afterNameMap = $this->getNodeNameMap($contextAfter);
        foreach ($addedNames as $name) {
            $node = $afterNameMap[$name];
            $fileAfter = $this->getFileName($contextAfter, $name, false);
            $this->reportAddedNode($report, $fileAfter, $contextAfter, $node);
        }
    }

    /**
     * Report moved or removed nodes
     *
     * @param Report $report
     * @param Node|Registry $contextBefore
     * @param Node|Registry $contextAfter
     * @param string[] $removedNames
     * @return void
     */
    protected function reportMovedOrRemoved($report, $contextBefore, $contextAfter, $removedNames)
    {
        $beforeNameMap = $this->getNodeNameMap($contextBefore);
        foreach ($removedNames as $name) {
            $nodeBefore = $beforeNameMap[$name];
            $fileBefore = $this->getFileName($contextBefore, $name, true);
            $fileAfter = $this->getFileName($contextAfter, $name, false);
            if ($fileAfter && $this->isMovedToParent(new ClassParser($fileAfter), $nodeBefore)) {
                $this->reportMovedNode($report, $fileBefore, $contextBefore, $nodeBefore);
            } else {
                $this->reportRemovedNode($report, $fileBefore, $contextBefore, $nodeBefore);
            }
        }
    }

    /**
     * Find changes to nodes that exist in both before and after states and add them to the report
     *
     * @param Report $report
     * @param Node|Registry $contextBefore
     * @param Node|Registry $contextAfter
     * @param string[] $toVerify
     * @return void
     */
    protected function reportChanged($report, $contextBefore, $contextAfter, $toVerify)
    {
        // Not all types have changes beyond add/remove
    }

    /**
     * Get the filename to use in the report.
     *
     * Class and Interface can override this to pull from context instead of class vars
     *
     * @param Node|Registry $context
     * @param string $nodeName
     * @param bool $isBefore
     * @return string|null
     */
    protected function getFileName($context, $nodeName, $isBefore = true)
    {
        return $isBefore ? $this->fileBefore : $this->fileAfter;
    }
}
