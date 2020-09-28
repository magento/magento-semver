<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Visitor;

use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;

/**
 * Create parent reference for nodes. Parent reference can be found in 'parent' node attribute.
 */
class ParentConnector extends NodeVisitorAbstract
{
    /**
     * Stack of nodes that used to create parent references
     *
     * @var array
     */
    private $stack;

    /**
     * @inheritDoc
     */
    public function beginTraverse(array $nodes)
    {
        $this->stack = [];
    }

    /**
     * @inheritDoc
     */
    public function enterNode(Node $node)
    {
        if (!empty($this->stack)) {
            $node->setAttribute('parent', $this->stack[count($this->stack) - 1]);
        }
        $this->stack[] = $node;
    }

    /**
     * @inheritDoc
     */
    public function leaveNode(Node $node)
    {
        array_pop($this->stack);
    }
}
