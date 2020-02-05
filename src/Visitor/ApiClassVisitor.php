<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Visitor;

use Magento\SemanticVersionChecker\Registry\ClassLikeNodeMetadata;
use PhpParser\Node;

class ApiClassVisitor extends AbstractApiVisitor
{
    /** @var string */
    protected $nodeType = '\PhpParser\Node\Stmt\Class_';

    public function add(Node $node, ClassLikeNodeMetadata $metadata)
    {
        $this->registry->addClassWithMetadata($node, $metadata);
    }
}
