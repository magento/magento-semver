<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionCheckr\Node\Xsd;

/**
 * Data object for XSD nodes.
 */
class ElementNode implements NodeInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var int
     */
    private $minOccurs;

    /**
     * @var int|null
     */
    private $maxOccurs;

    /**
     * Constructor.
     *
     * @param string $name
     * @param string $type
     * @param int $minOccurs
     * @param int|null $maxOccurs
     */
    public function __construct(string $name, string $type, int $minOccurs, ?int $maxOccurs)
    {
        $this->name      = $name;
        $this->type      = $type;
        $this->minOccurs = $minOccurs;
        $this->maxOccurs = $maxOccurs;
    }

    /**
     * Getter for {@link Node::$maxOccurs}.
     *
     * Returns <kbd>null</kbd> in case there is no upper bound fo occurrences of this node.
     *
     * @return int|null
     */
    public function getMaxOccurs(): ?int
    {
        return $this->maxOccurs;
    }

    /**
     * Getter for {@link Node::$minOccurs}.
     *
     * @return int
     */
    public function getMinOccurs(): int
    {
        return $this->minOccurs;
    }

    /**
     * Getter for {@link Node::$name}.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Getter for {@link Node::$type}.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Returns the unique key for current node
     *
     * @return string
     */
    public function getUniqueKey(): string
    {
        return $this->getType() . '/' . $this->getName();
    }

    /**
     * Returns whether the entity is required.
     *
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->getMinOccurs() > 0;
    }
}
