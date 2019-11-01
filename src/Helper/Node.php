<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionCheckr\Helper;

use Magento\SemanticVersionCheckr\SemanticVersionChecker;
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

    /**
     * Workaround for different versions of `nikic/php-parser`.
     *
     * Later versions of the package `nikic/php-parser` add the convenience method `ClassLike::getTraitUses()`. If we
     * happen to have such a version, that method is called and its result is returned, otherwise we extract the
     * {@link \PhpParser\Node\Stmt\TraitUse} and return them.
     *
     * @param PhpNode $node
     * @return TraitUse[]
     */
    public function getTraitUses(PhpNode $node): array
    {
        if (method_exists($node, 'getTraitUses')) {
            return $node->getTraitUses();
        }

        $traitUses = [];
        foreach ($node->stmts as $stmt) {
            if ($stmt instanceof TraitUse) {
                $traitUses[] = $stmt;
            }
        }
        return $traitUses;
    }
}
