<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Helper;

use Magento\SemanticVersionChecker\SemanticVersionChecker;
use PhpParser\Node as PhpNode;
use PhpParser\Node\Stmt\TraitUse;

/**
 * Implements a helper that deals with nodes.
 */
class Node
{
    /**
     * Returns whether `$node` is considered API relevant.
     *
     * @param PhpNode $node
     * @return bool
     */
    public function isApiNode(PhpNode $node)
    {
        $comment = $node->getAttribute('comments');

        return isset($comment[0])
               && strpos($comment[0]->getText(), SemanticVersionChecker::ANNOTATION_API) !== false;
    }

}
