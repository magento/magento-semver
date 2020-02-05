<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Visitor;

use Magento\SemanticVersionChecker\Registry\ClassLikeNodeMetadata;
use PhpParser\Node;

class ApiInterfaceVisitor extends AbstractApiVisitor
{
    /** @var string */
    protected $nodeType = '\PhpParser\Node\Stmt\Interface_';

    public function add(Node $node, ClassLikeNodeMetadata $nodeMetadata)
    {
        $this->registry->addInterfaceWithMetadata($node, $nodeMetadata);
    }
}
