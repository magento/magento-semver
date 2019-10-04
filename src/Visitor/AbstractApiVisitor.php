<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tools\SemanticVersionChecker\Visitor;

use Magento\Tools\SemanticVersionChecker\SemanticVersionChecker;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PHPSemVerChecker\Registry\Registry;

abstract class AbstractApiVisitor extends NodeVisitorAbstract
{
    /** @var string */
    protected $nodeType;

    /** @var Registry */
    protected $registry;

    /**
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param Node $node
     * @return void
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
        if (!$this->isApiNode($classNode)) {
            $apiNodes = [];
            foreach ($classNode->stmts as $classSubNode) {
                if ($this->isApiNode($classSubNode)) {
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
     * @return bool
     */
    private function isApiNode(Node $node)
    {
        return ($comment = $node->getAttribute('comments'))
        && isset($comment[0])
        && strpos($comment[0]->getText(), SemanticVersionChecker::ANNOTATION_API) !== false;
    }

    /**
     * @param Node $node
     * @return mixed
     */
    abstract public function add(Node $node);
}
