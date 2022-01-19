<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\ClassHierarchy;

use Magento\SemanticVersionChecker\Helper\Node as NodeHelper;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_ as ClassNode;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_ as InterfaceNode;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_ as TraitNode;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

/**
 * Implements a visitor for `class`, `interface` and `trait` nodes that generates a dependency graph.
 */
class DependencyInspectionVisitor extends NodeVisitorAbstract
{
    /** @var DependencyGraph */
    private $dependencyGraph;

    /** @var NodeHelper */
    private $nodeHelper;

    /**
     * @var Entity
     * Holds current Entity. Stored so we can populate this entity in our dependency graph upon walking relevant child
     * nodes.
     */
    private $currentClassLike = null;

    /**
     * Constructor.
     *
     * @param DependencyGraph $dependencyGraph
     * @param NodeHelper $nodeHelper
     */
    public function __construct(DependencyGraph $dependencyGraph, NodeHelper $nodeHelper)
    {
        $this->dependencyGraph = $dependencyGraph;
        $this->nodeHelper      = $nodeHelper;
    }

    /**
     * Logic to process current node. We aggressively halt walking the AST since this may contain many nodes
     * If we are visiting a Classlike node, set currentClassLike so we can populate this entity in our dependency graph
     * upon walking relevant child nodes like PropertyProperty and ClassMethod.
     *
     * Subparse tree we want to traverse will be something like:
     * Namespace -> ClassLike -> ClassMethod
     *                        -> TraitUse
     *                        -> PropertyProperty
     *
     *
     * @inheritdoc
     *
     * @param Node $node
     * @return int tells NodeTraverser whether to continue traversing
     */
    public function enterNode(Node $node)
    {
        switch (true) {
            case $node instanceof Node\Stmt\Namespace_:
                return null;
            case $node instanceof ClassLike:
                //set currentClassLike entity
                return $this->handleClassLike($node);
            case $node instanceof ClassMethod:
                $this->currentClassLike->addMethod($node);
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            case $node instanceof TraitUse:
                foreach ($node->traits as $trait) {
                    $traitName   = (string)$trait;
                    $traitEntity = $this->dependencyGraph->findOrCreateTrait($traitName);
                    $this->currentClassLike->addUses($traitEntity);
                }
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            case $node instanceof Property:
                $this->currentClassLike->addProperty($node);
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            default:
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }
    }

    /**
     * Handles Class, Interface, and Traits nodes. Sets currentClassLike entity and will populate extends, implements,
     * and API information
     *
     * @param ClassLike $node
     * @return int|null
     */
    private function handleClassLike(ClassLike $node)
    {
        /**
         * @var \PhpParser\Node\Name $namespacedName
         * This is set in the NamespaceResolver visitor
         */
        $namespacedName = $node->namespacedName;
        switch (true) {
            case $node instanceof ClassNode:
                if ($node->isAnonymous()) {
                    return NodeTraverser::STOP_TRAVERSAL;
                }
                $this->currentClassLike = $this->dependencyGraph->findOrCreateClass((string)$namespacedName);
                if ($node->extends) {
                    $parentClassName = (string)$node->extends;
                    $parentClassEntity = $this->dependencyGraph->findOrCreateClass($parentClassName);
                    $this->currentClassLike->addExtends($parentClassEntity);
                }
                foreach ($node->implements as $implement) {
                    $interfaceName = (string)$implement;
                    $interfaceEntity = $this->dependencyGraph->findOrCreateInterface($interfaceName);
                    $this->currentClassLike->addImplements($interfaceEntity);
                }
                break;
            case $node instanceof InterfaceNode:
                $this->currentClassLike = $this->dependencyGraph->findOrCreateInterface((string)$namespacedName);
                foreach ($node->extends as $extend) {
                    $interfaceName = (string)$extend;
                    $interfaceEntity = $this->dependencyGraph->findOrCreateInterface($interfaceName);
                    $this->currentClassLike->addExtends($interfaceEntity);
                }
                break;
            case $node instanceof TraitNode:
                $this->currentClassLike = $this->dependencyGraph->findOrCreateTrait((string)$namespacedName);
                break;
        }
        $this->currentClassLike->setIsApi($this->nodeHelper->isApiNode($node));
        return null;
    }

    /*
     * Unsets currentClassLike upon exiting ClassLike node. This is for cleanup, although this is not necessary since
     * Classmethod, PropertyProperty, and TraitUse nodes will only be traversed after Classlike
     *
     * @param Node $node
     * @return false|int|Node|Node[]|void|null
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof ClassLike) {
            $this->currentClassLike = null;
        }
    }

    /**
     * Getter for {@link DependencyInspectionVisitor::$dependencyGraph}.
     *
     * @return DependencyGraph
     */
    public function getDependencyGraph(): DependencyGraph
    {
        return $this->dependencyGraph;
    }
}
