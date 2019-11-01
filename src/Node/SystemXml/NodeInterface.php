<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionCheckr\Node\SystemXml;

/**
 * Data Object interface for system configuration elements.
 */
interface NodeInterface
{
    /**
     * Defines the path separator that is used to build the nodes path.
     */
    const PATH_SEPARATOR = '/';

    /**
     * Returns the parent of current element if it has one.
     *
     * @return NodeInterface|null
     */
    public function getParent(): ?NodeInterface;

    /**
     * Return the path of current element.
     *
     * @return string
     */
    public function getPath(): string;

    /**
     * Returns a unique key for current node.
     *
     * @return string
     */
    public function getUniqueKey(): string;
}
