<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionCheckr\Node\Layout;

/**
 * Data Object for layout element type <pre><block><pre>
 */
class Block implements LayoutNodeInterface
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $template;

    /**
     * @var bool
     */
    private $cacheable;

    /**
     * @param string $name
     * @param string $class
     * @param string $template
     * @param bool $cacheable
     */
    public function __construct(string $name, string $class, string $template, bool $cacheable = true)
    {
        $this->name = $name;
        $this->class = $class;
        $this->template = $template;
        $this->cacheable = $cacheable;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @return bool
     */
    public function isCacheable(): bool
    {
        return $this->cacheable;
    }

    /**
     * @return string
     */
    public function getUniqueKey(): string
    {
        return $this->name;
    }
}
