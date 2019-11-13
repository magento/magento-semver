<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Node\Xsd;

/**
 * Data object for XSD attributes.
 */
class AttributeNode implements NodeInterface
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
     * @var bool
     */
    private $required;

    /**
     * Constructor.
     *
     * @param string $name
     * @param string $type
     * @param bool $required
     */
    public function __construct(string $name, string $type, bool $required)
    {
        $this->name = $name;
        $this->type = $type;
        $this->required = $required;
    }

    /**
     * Getter for {@link Attribute::$name}.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Getter for {@link Attribute::$type}.
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
        return '@' . $this->getType() . '/' . $this->getName();
    }

    /**
     * Getter for {@link Attribute::$required}.
     *
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }
}
