<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Node\Xsd;

/**
 * Defines an interface for all entities of an XSD file.
 */
interface NodeInterface
{
    /**
     * Returns the content of the <kbd>name</kbd> attribute of the node.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Returns the content of the <kbd>type</kbd> attribute of the node.
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Returns the unique key for current node
     *
     * @return string
     */
    public function getUniqueKey(): string;

    /**
     * Returns whether the entity is required.
     *
     * @return bool
     */
    public function isRequired(): bool;
}
