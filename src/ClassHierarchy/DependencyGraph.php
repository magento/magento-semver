<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionCheckr\ClassHierarchy;

/**
 * Implements a dependency graph that stores {@link Entity} nodes and allows to find them by name and type.
 */
class DependencyGraph
{
    /**
     * Stores entities by name.
     *
     * @var array
     */
    private $entitiesByName = [];

    /**
     * @var EntityFactory
     */
    private $entityFactory;

    /**
     * Constructor.
     *
     * @param EntityFactory $entityFactory
     */
    public function __construct(EntityFactory $entityFactory)
    {
        $this->entityFactory = $entityFactory;
    }

    /**
     * Adds <var>$entity</var> to graph.
     *
     * @param Entity $entity
     */
    public function addEntity(Entity $entity): void
    {
        $name                        = $entity->getName();
        $this->entitiesByName[$name] = $entity;
    }

    /**
     * @param string $fullyQualifiedName
     * @return Entity|null
     */
    public function findEntityByName(string $fullyQualifiedName): ?Entity
    {
        return $this->entitiesByName[$fullyQualifiedName] ?? null;
    }

    /**
     * @param string $fullyQualifiedName
     * @return Entity
     */
    public function findOrCreateInterface(string $fullyQualifiedName): Entity
    {
        $interface = $this->findEntityByName($fullyQualifiedName);

        if (!$interface) {
            $interface = $this->entityFactory->createInterface($fullyQualifiedName);
            $this->addEntity($interface);
        }

        return $interface;
    }

    /**
     * @param string $fullyQualifiedName
     * @return Entity
     */
    public function findOrCreateClass(string $fullyQualifiedName): Entity
    {
        $class = $this->findEntityByName($fullyQualifiedName);

        if (!$class) {
            $class = $this->entityFactory->createClass($fullyQualifiedName);
            $this->addEntity($class);
        }

        return $class;
    }

    /**
     * @param string $fullyQualifiedName
     * @return Entity
     */
    public function findOrCreateTrait(string $fullyQualifiedName): Entity
    {
        $trait = $this->findEntityByName($fullyQualifiedName);

        if (!$trait) {
            $trait = $this->entityFactory->createTrait($fullyQualifiedName);
            $this->addEntity($trait);
        }

        return $trait;
    }
}
