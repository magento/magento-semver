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
use PhpParser\Node\Stmt\Interface_ as InterfaceNode;
use PhpParser\Node\Stmt\Trait_ as TraitNode;
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
     * @inheritDoc
     *
     * Inspect nodes after all visitors have run since we need the fully qualified names of nodes.
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof ClassNode) {
            $this->addClassNode($node);
        } elseif ($node instanceof InterfaceNode) {
            $this->addInterfaceNode($node);
        } elseif ($node instanceof TraitNode) {
            $this->addTraitNode($node);
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

    /**
     * @param ClassNode $node
     */
    private function addClassNode(ClassNode $node)
    {
        // name is not set for anonymous classes, therefore they cannot be part of the dependency graph
        if ($node->isAnonymous()) {
            return;
        }

        $className = (string)$node->namespacedName;
        $class     = $this->dependencyGraph->findOrCreateClass($className);

        $class->setIsApi($this->nodeHelper->isApiNode($node));

        if ($node->extends) {
            $parentClassName   = (string)$node->extends;
            $parentClassEntity = $this->dependencyGraph->findOrCreateClass($parentClassName);
            $class->addExtends($parentClassEntity);
        }

        foreach ($node->implements as $implement) {
            $interfaceName   = (string)$implement;
            $interfaceEntity = $this->dependencyGraph->findOrCreateInterface($interfaceName);
            $class->addImplements($interfaceEntity);
        }

        foreach ($this->nodeHelper->getTraitUses($node) as $traitUse) {
            foreach ($traitUse->traits as $trait) {
                $traitName   = (string)$trait;
                $traitEntity = $this->dependencyGraph->findOrCreateTrait($traitName);
                $class->addUses($traitEntity);
            }
        }

        $this->dependencyGraph->addEntity($class);
    }

    /**
     * @param InterfaceNode $node
     */
    private function addInterfaceNode(InterfaceNode $node)
    {
        $interfaceName = (string)$node->namespacedName;
        $interface     = $this->dependencyGraph->findOrCreateInterface($interfaceName);

        $interface->setIsApi($this->nodeHelper->isApiNode($node));

        foreach ($node->extends as $extend) {
            $interfaceName   = (string)$extend;
            $interfaceEntity = $this->dependencyGraph->findOrCreateInterface($interfaceName);
            $interface->addExtends($interfaceEntity);
        }

        $this->dependencyGraph->addEntity($interface);
    }

    /**
     * @param TraitNode $node
     */
    private function addTraitNode(TraitNode $node)
    {
        $traitName = (string)$node->namespacedName;
        $trait     = $this->dependencyGraph->findOrCreateTrait($traitName);

        $trait->setIsApi($this->nodeHelper->isApiNode($node));

        foreach ($this->nodeHelper->getTraitUses($node) as $traitUse) {
            foreach ($traitUse->traits as $parentTrait) {
                $parentTraitName   = (string)$parentTrait;
                $parentTraitEntity = $this->dependencyGraph->findOrCreateTrait($parentTraitName);
                $trait->addUses($parentTraitEntity);
            }
        }

        $this->dependencyGraph->addEntity($trait);
    }
}
