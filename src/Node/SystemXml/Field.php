<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Node\SystemXml;

/**
 * Data object for system configuration <kbd>field</kbd> element.
 */
class Field implements NodeInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var NodeInterface
     */
    private $parent;

    /**
     * Constructor.
     *
     * @param string $id
     * @param NodeInterface $parent
     */
    public function __construct(string $id, NodeInterface $parent)
    {
        $this->id     = $id;
        $this->parent = $parent;
    }

    /**
     * Getter for {@link Field::$id}.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Returns the parent of current element if it has one.
     *
     * @return NodeInterface|null
     */
    public function getParent(): ?NodeInterface
    {
        return $this->parent;
    }

    /**
     * Return the path of current element.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->getParent()->getPath() . self::PATH_SEPARATOR . $this->getId();
    }

    /**
     * Returns a unique key for current node.
     *
     * @return string
     */
    public function getUniqueKey(): string
    {
        return 'field:' . $this->getPath();
    }
}
