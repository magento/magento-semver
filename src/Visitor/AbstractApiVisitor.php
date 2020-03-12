<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Visitor;

use Magento\SemanticVersionChecker\ClassHierarchy\DependencyGraph;
use Magento\SemanticVersionChecker\Helper\Node as NodeHelper;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PHPSemVerChecker\Registry\Registry;

abstract class AbstractApiVisitor extends NodeVisitorAbstract
{
    /** @var string */
    protected $nodeType;

    /** @var Registry */
    protected $registry;

    /** @var DependencyGraph */
    private $dependencyGraph;

    /** @var NodeHelper */
    private $nodeHelper;

    /**
     * @param Registry $registry
     * @param NodeHelper $nodeHelper
     * @param DependencyGraph|null $dependencyGraph
     */
    public function __construct(
        Registry $registry,
        NodeHelper $nodeHelper,
        DependencyGraph $dependencyGraph = null
    ) {
        $this->dependencyGraph = $dependencyGraph;
        $this->nodeHelper      = $nodeHelper;
        $this->registry        = $registry;
    }

    /**
     * Halt walking when we reach Classlike node
     * @param Node $node
     * @return int
     */
    public function enterNode(Node $node)
    {
        switch (true) {
            case $node instanceof Node\Stmt\Namespace_:
            case $node instanceof Node\Stmt\ClassLike:
                return null;
            default:
                /*
                 * Note that by skipping traversal of ClassMethod children, NameResolver will not resolve namespaces on
                 * its method stmts. This will affect analyzing for ClassMethodImplementationChanged in
                 * src/Analyzer/ClassMethodAnalyzer.php
                 * For example changing:
                 *     a = \Magento\Framework\App\ObjectManager::getInstance();
                 * To:
                 *     a =  ObjectManager::getInstance();
                 * will now be analyzed as a ClassMethodImplementationChanged (a PATCH change).
                 */
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }
    }

    /**
     * @inheritdoc
     */
    public function leaveNode(Node $node)
    {
        if (is_a($node, $this->nodeType)) {
            if ($node = $this->pruneNonApiNodes($node)) {
                $this->add($node);
            }
        }
    }

    /**
     * Remove non-api-annotated nodes (properties, methods, constants) from a class node; remove entire node if
     * there are no api-annotations.
     *
     * @param Node $classNode
     * @return Node|null
     */
    public function pruneNonApiNodes(Node $classNode)
    {
        $entity = $this->dependencyGraph
            ? $this->dependencyGraph->findEntityByName((string)$classNode->namespacedName)
            : null;

        if ($entity) {
            $isApi = $entity->isApi() || $entity->hasApiDescendant();
        } else {
            $isApi = $this->nodeHelper->isApiNode($classNode);
        }

        if (!$isApi) {
            $apiNodes = [];
            foreach ($classNode->stmts as $classSubNode) {
                if ($this->nodeHelper->isApiNode($classSubNode)) {
                    $apiNodes[] = $classSubNode;
                }
            }
            if ($apiNodes) {
                $classNode->stmts = $apiNodes;
            } else {
                $classNode = null;
            }
        }
        return $classNode;
    }

    /**
     * @param Node $node
     * @return mixed
     */
    abstract public function add(Node $node);
}
