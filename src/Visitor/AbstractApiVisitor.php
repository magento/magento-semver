<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Visitor;

use Magento\SemanticVersionChecker\ClassHierarchy\DependencyGraph;
use Magento\SemanticVersionChecker\Helper\Node as NodeHelper;
use Magento\SemanticVersionChecker\Registry\ClassLikeNodeMetadata;
use Magento\SemanticVersionChecker\Registry\PhpRegistry;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PHPSemVerChecker\Registry\Registry;

/**
 * Class AbstractApiVisitor
 * Will add information about node to NodeMetadata and add both node and metadata to registry. This will only add API
 * nodes.
 * Additionally will prune stmts for all nodes for memory and performance.
 * @package Magento\SemanticVersionChecker\Visitor
 */
abstract class AbstractApiVisitor extends NodeVisitorAbstract
{
    /** @var string */
    protected $nodeType;

    /** @var PhpRegistry */
    protected $registry;

    /** @var DependencyGraph */
    private $dependencyGraph;

    /** @var NodeHelper */
    private $nodeHelper;

    /** @var Node\Stmt\ClassLike */
    private $currentClassLike;

    /** @var ClassLikeNodeMetadata */
    private $currentNodeMetadata;

    /** @var boolean */
    private $isApi;

    /**
     * @param Registry $registry
     * @param NodeHelper $nodeHelper
     * @param DependencyGraph|null $dependencyGraph
     */
    public function __construct(
        PhpRegistry $registry,
        NodeHelper $nodeHelper,
        DependencyGraph $dependencyGraph = null
    ) {
        $this->dependencyGraph = $dependencyGraph;
        $this->nodeHelper      = $nodeHelper;
        $this->registry        = $registry;
    }

    /**
     * Handle node parsing. Adds methods and traits variable dynamically to $node
     * @param Node $node
     * @return int
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_) {
            return null;
        }
        if ($node instanceof  Node\Stmt\ClassLike) {
            $name = (string)$node->namedspacedName;
            $entity = $this->dependencyGraph
                ? $this->dependencyGraph->findEntityByName($name)
                : null;

            //only save API classes
            if ($entity) {
                $this->isApi = $entity->isApi() || $entity->hasApiDescendant();
            } else {
                $this->isApi = $this->nodeHelper->isapinode($node);
            }
            if (!$this->isApi) {

                return NodeTraverser::STOP_TRAVERSAL;
            }

            //store information about parent for tranversal
            $this->currentClassLike = $node;
            $this->currentNodeMetadata = new ClassLikeNodeMetadata();
            return null;
        }

        if ($node instanceof Node\Stmt\TraitUse) {
            $this->currentNodeMetadata->setTraits($node->traits);
            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }
        if ($node instanceof Node\Stmt\ClassMethod) {
            //hash and prune statements before storing
            $this->currentNodeMetadata->addMethod($node);
            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }
        return NodeTraverser::DONT_TRAVERSE_CHILDREN;
    }

    /**
     * @param Node $node
     * @return void
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\ClassLike) {
            if($this->isApi) {
                //prune stmts for performance reasons
                $node->stmts = [];
                $this->add($node, $this->currentNodeMetadata);
            }
            //Clean state variables after leaving node.
            $this->currentClassLike = null;
            $this->currentNodeMetadata = null;
        }
    }


    /**
     * @param Node $node
     * @return mixed
     */
    abstract public function add(Node $node, ClassLikeNodeMetadata $nodeMetadata);
}
