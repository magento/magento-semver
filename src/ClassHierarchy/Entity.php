<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\ClassHierarchy;

use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;

/**
 * Implements an entity that reflects a `class`, `interface` or `trait` and its dependencies.
 */
class Entity
{
    /**#@+
     * Defines the valid entity types
     */
    public const TYPE_CLASS     = 'class';
    public const TYPE_INTERFACE = 'interface';
    public const TYPE_TRAIT     = 'trait';
    /**#@-*/

    /**
     * Stores the fully qualified name of the entity.
     *
     * @var string
     */
    private $name;

    /**
     * Stores the type of current entity.
     *
     * @var string
     */
    private $type;

    /**
     * Holds references to the entities that current entity extends.
     *
     * In case of a `class` a sinlge reference is held, while an `interface` can extend multiple other `interface`s.
     *
     * @var Entity[]
     */
    private $extends = [];

    /**
     * Holds references to the entities that `extend` current entity.
     *
     * @var Entity[]
     */
    private $extendedBy = [];

    /**
     * Holds referenced to the `interface` entities that are implemented by current entity.
     *
     * @var Entity[]
     */
    private $implements = [];

    /**
     * Holds references to the `interface` entities that implement current entity.
     *
     * @var Entity[]
     */
    private $implementedBy = [];

    /**
     * Stores whether current entity is API relevant.
     *
     * @var bool
     */
    private $isApi;

    /**
     * Holds references to the `trait` entities that are used by current entity.
     *
     * @var Entity[]
     */
    private $uses = [];

    /**
     * Holds references to the entities that use current `trait` entity.
     *
     * @var Entity[]
     */
    private $usedBy = [];

    /**
     * @var ClassMethod[]
     */
    private $methodList = [];

    /**
     * @var Property[]
     */
    private $propertyList = [];

    /**
     * Constructor.
     *
     * @param string $name
     * @param string $type
     */
    public function __construct(string $name, string $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    /*
     * Getter / Setter
     */

    /**
     * Getter for {@link Entity::$isApi}.
     *
     * @return bool
     */
    public function isApi(): bool
    {
        return $this->isApi;
    }

    /**
     * Setter for {@link Entity::$isApi}.
     *
     * @param bool $isApi
     */
    public function setIsApi(bool $isApi)
    {
        $this->isApi = $isApi;
    }

    /**
     * Getter for {@link Entity::$extends}.
     *
     * @return Entity[]
     */
    public function getExtends(): array
    {
        return $this->extends;
    }

    /**
     * Getter for {@link Entity::$extendedBy}.
     *
     * @return Entity[]
     */
    public function getExtendedBy(): array
    {
        return $this->extendedBy;
    }

    /**
     * Getter for {@link Entity::$implements}.
     *
     * @return Entity[]
     */
    public function getImplements(): array
    {
        return $this->implements;
    }

    /**
     * Getter for {@link Entity::$implementedBy}.
     *
     * @return Entity[]
     */
    public function getImplementedBy(): array
    {
        return $this->implementedBy;
    }

    /**
     * Getter for {@link Entity::$name}.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Getter for {@link Entity::$uses}.
     *
     * @return Entity[]
     */
    public function getUses(): array
    {
        return $this->uses;
    }

    /**
     * Getter for {@link Entity::$usedBy}.
     *
     * @return Entity[]
     */
    public function getUsedBy(): array
    {
        return $this->usedBy;
    }

    /*
     * Public methods
     */

    /**
     * Adds `$entity` to the list of extended entities.
     *
     * @param Entity $entity
     */
    public function addExtends(Entity $entity): void
    {
        $key                 = $entity->getName();
        $this->extends[$key] = $entity;
        $entity->addExtendedBy($this);
    }

    /**
     * Adds `$interface` entity to list of implemented `interface` entities.
     *
     * @param Entity $interface
     */
    public function addImplements(Entity $interface): void
    {
        $key                    = $interface->getName();
        $this->implements[$key] = $interface;
        $interface->addImplementedBy($this);
    }

    /**
     * Adds `$trait` entity to list of used `trait` entities.
     *
     * @param Entity $trait
     */
    public function addUses(Entity $trait): void
    {
        $key              = $trait->getName();
        $this->uses[$key] = $trait;
        $trait->addUsedBy($this);
    }

    /**
     * Returns whether current entity has an ancestor that is API relevant.
     *
     * @return bool
     */
    public function hasApiAncestor(): bool
    {
        /** @var Entity[] $parents */
        $parents = array_merge(
            $this->getExtends(),
            $this->getImplements(),
            $this->getUses()
        );

        //check whether any parent matches the condition
        foreach ($parents as $parent) {
            if ($parent->isApi() || $parent->hasApiAncestor()) {
                return true;
            }
        }

        //if we came here, the condition was never met
        return false;
    }

    /**
     * Returns whether current entity has a descendant that is API relevant.
     *
     * @return bool
     */
    public function hasApiDescendant(): bool
    {
        /** @var Entity[] $children */
        $children = array_merge(
            $this->getExtendedBy(),
            $this->getImplementedBy(),
            $this->getUsedBy()
        );

        //check whether any descendant matches the condition
        foreach ($children as $child) {
            if ($child->isApi() || $child->hasApiDescendant()) {
                return true;
            }
        }

        //if we came here, the condition was never met
        return false;
    }

    /**
     * Returns whether current entity reflects a `class`.
     *
     * @return bool
     */
    public function isClass(): bool
    {
        return $this->type === self::TYPE_CLASS;
    }

    /**
     * Retturns whether current entity reflects an `interface`.
     *
     * @return bool
     */
    public function isInterface(): bool
    {
        return $this->type === self::TYPE_INTERFACE;
    }

    /**
     * Reflects whether current entity reflects a `trait`.
     *
     * @return bool
     */
    public function isTrait(): bool
    {
        return $this->type === self::TYPE_TRAIT;
    }

    /*
     * Private methods
     */

    /**
     * Adds `$entity` to the list of entities that extend current entity.
     *
     * @param Entity $entity
     */
    private function addExtendedBy(Entity $entity)
    {
        $key                    = $entity->getName();
        $this->extendedBy[$key] = $entity;
    }

    /**
     * Adds `$entity` to the list of entities that implement current `interface` entity.
     *
     * @param Entity $entity
     */
    private function addImplementedBy(Entity $entity)
    {
        $key                       = $entity->getName();
        $this->implementedBy[$key] = $entity;
    }

    /**
     * Adds `$entity` to the list of entities that usese current `trait` entity.
     *
     * @param Entity $entity
     */
    private function addUsedBy(Entity $entity)
    {
        $key                = $entity->getName();
        $this->usedBy[$key] = $entity;
    }

    /**
     * @param ClassMethod[] $methodList
     */
    public function setMethodList(array $methodList): void
    {
        $this->methodList = [];
        foreach ($methodList as $method) {
            $this->addMethod($method);
        }
    }

    /**
     * Also cleans method to prevent memory leaks.
     * @param ClassMethod $method
     */
    public function addMethod(ClassMethod $method): void
    {
        //remove stmts from Method
        $method->stmts = [];
        $this->methodList[$method->name->toString()] = $method;
    }

    /**
     * @param PropertyProperty $property
     */
    public function addProperty(PropertyProperty $property): void
    {
        $this->propertyList[$property->name] = $property;
    }

    /**
     * @param Property[] $propertyList
     */
    public function setPropertyList(array $propertyList): void
    {
        $this->propertyList = [];
        foreach ($propertyList as $property) {
            $this->addProperty($property);
        }
    }

    /**
     * @return ClassMethod[]
     */
    public function getMethodList(): array
    {
        return $this->methodList;
    }

    /**
     * @return Property[]
     */
    public function getPropertyList(): array
    {
        return $this->propertyList;
    }
}
