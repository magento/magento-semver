<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Node\SystemXml;

/**
 * Data object for system configuration <kbd>section</kbd> element.
 */
class Section implements NodeInterface
{
    /**
     * The <kbd>id</kbd> attribute of the section node.
     *
     * @var string
     */
    private $id;

    /**
     * Constructor.
     *
     * @param string $id
     */
    public function __construct(string $id)
    {
        $this->id = $id;
    }

    /**
     * Getter for {@link Section::$id}.
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
        //sections are not contained in any other nodes that we inspect
        return null;
    }

    /**
     * Return the path of current element.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->getId();
    }

    /**
     * Returns a unique key for current node.
     *
     * @return string
     */
    public function getUniqueKey(): string
    {
        return 'section:' . $this->getPath();
    }
}
