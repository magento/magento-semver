<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Helper;

use Magento\SemanticVersionChecker\SemanticVersionChecker;
use PhpParser\Comment\Doc as DocComment;
use PhpParser\Node as PhpNode;

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
        $comments = $node->getAttribute('comments');

        $result = false;
        if (is_array($comments) && !empty($comments)) {
            foreach ($comments as $comment) {
                if ($comment instanceof DocComment) {
                    $result = (strpos($comment->getText(), SemanticVersionChecker::ANNOTATION_API) !== false);
                    if ($result) {
                        break;
                    }
                }
            }
        }

        return $result;
    }
}
