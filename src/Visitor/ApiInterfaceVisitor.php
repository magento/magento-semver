<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionCheckr\Visitor;

use PhpParser\Node;

class ApiInterfaceVisitor extends AbstractApiVisitor
{
    /** @var string */
    protected $nodeType = '\PhpParser\Node\Stmt\Interface_';

    public function add(Node $node)
    {
        $this->registry->addInterface($node);
    }
}
