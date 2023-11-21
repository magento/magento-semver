<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\ClassHierarchy;

/**
 * Implements a factory that creates new instances of {@link Entity}.
 */
class EntityFactory
{
    /**
     * @param string $name
     * @return Entity
     */
    public function createClass(string $name): Entity
    {
        return new Entity($name, Entity::TYPE_CLASS);
    }

    /**
     * @param string $name
     * @return Entity
     */
    public function createInterface(string $name): Entity
    {
        return new Entity($name, Entity::TYPE_INTERFACE);
    }

    /**
     * @param string $name
     * @return Entity
     */
    public function createTrait(string $name): Entity
    {
        return new Entity($name, Entity::TYPE_TRAIT);
    }
}
