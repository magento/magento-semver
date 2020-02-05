<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Visitor;

use Magento\SemanticVersionChecker\Registry\ClassLikeNodeMetadata;
use PhpParser\Node;
use PhpParser\Node\Stmt\Trait_;

/**
 * Defines visitor that sorts out non-API traits
 */
class ApiTraitVisitor extends AbstractApiVisitor
{

    /** @var string  */
    protected $nodeType = Trait_::class;

    /**
     * Adds <var>$trait</var> to registry.
     *
     * @param Node|Trait_ $trait
     */
    public function add(Node $trait, ClassLikeNodeMetadata $nodeMetadata)
    {
        $this->registry->addTraitWithMetadata($trait, $nodeMetadata);
    }
}
