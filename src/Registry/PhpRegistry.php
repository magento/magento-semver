<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Registry;


use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PHPSemVerChecker\Registry\Registry;

class PhpRegistry extends Registry
{
    /**
     * A list of metadata about the node
     *
     * @var array
     */
    public $metadata = [];

    public function __construct()
    {
        parent::__construct();
    }

    public function addFunctionWithMetadata(Function_ $function, ClassLikeNodeMetadata $nodeMetadata)
    {
        $this->addNodeWithMetadata('function', $function, $nodeMetadata);
    }

    public function addInterfaceWithMetadata(Interface_ $interface, ClassLikeNodeMetadata $nodeMetadata)
    {
        $this->addNodeWithMetadata('interface', $interface, $nodeMetadata);
    }

    public function addTraitWithMetadata(Trait_ $trait, ClassLikeNodeMetadata $nodeMetadata)
    {
        $this->addNodeWithMetadata('trait', $trait, $nodeMetadata);
    }

    public function addClassWithMetadata(Class_ $class, ClassLikeNodeMetadata $nodeMetadata)
    {
        $this->addNodeWithMetadata('class', $class, $nodeMetadata);
    }

    /**
     * @param $context
     * @param Stmt $node
     */
    protected function addNodeWithMetadata($context, Stmt $node, ClassLikeNodeMetadata $nodeMetadata) {
        parent::addNode($context, $node);
        $fullyQualifiedName = $this->fullyQualifiedName($node);
        $this->metadata[$context][$fullyQualifiedName] = $nodeMetadata;
    }
}