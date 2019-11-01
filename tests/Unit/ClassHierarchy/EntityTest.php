<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionCheckr\Test\Unit\ClassHierarchy;

use Magento\SemanticVersionCheckr\ClassHierarchy\Entity;
use PHPUnit\Framework\TestCase;

/**
 * Contains unit test cases for {@link \Magento\SemanticVersionCheckr\ClassHierarchy\Entity}
 */
class EntityTest extends TestCase
{
    /*
     * Tests
     */

    public function testAddExtends_ClassExtendsClass_AppliesReverseLink()
    {
        $entities = $this->createEntities(
            [
                ['ParentClass', Entity::TYPE_CLASS],
                ['ChildClass', Entity::TYPE_CLASS],
            ]
        );

        $entities['ChildClass']->addExtends($entities['ParentClass']);

        $parentExtendedBy = $entities['ParentClass']->getExtendedBy();
        $this->assertCount(1, $parentExtendedBy);
        $this->assertArrayHasKey('ChildClass', $parentExtendedBy);
        $this->assertSame($entities['ChildClass'], $parentExtendedBy['ChildClass']);
    }

    public function testAddExtends_InterfaceExtendsInterface_AppliesReverseLink()
    {
        $entities = $this->createEntities(
            [
                ['ParentInterface', Entity::TYPE_INTERFACE],
                ['ChildInterface', Entity::TYPE_INTERFACE],
            ]
        );

        $entities['ChildInterface']->addExtends($entities['ParentInterface']);

        $parentExtendedBy = $entities['ParentInterface']->getExtendedBy();
        $this->assertCount(1, $parentExtendedBy);
        $this->assertArrayHasKey('ChildInterface', $parentExtendedBy);
        $this->assertSame($entities['ChildInterface'], $parentExtendedBy['ChildInterface']);
    }

    public function testAddImplements_ClassImplementsInterface_AppliesReverseLink()
    {
        $entities = $this->createEntities(
            [
                ['MyInterface', Entity::TYPE_INTERFACE],
                ['MyClass', Entity::TYPE_CLASS],
            ]
        );

        $entities['MyClass']->addImplements($entities['MyInterface']);

        $interfaceImplementedBy = $entities['MyInterface']->getImplementedBy();
        $this->assertCount(1, $interfaceImplementedBy);
        $this->assertArrayHasKey('MyClass', $interfaceImplementedBy);
        $this->assertSame($entities['MyClass'], $interfaceImplementedBy['MyClass']);
    }

    public function testAddUses_ClassUsesTrait_AppliesReverseLink()
    {
        $entities = $this->createEntities(
            [
                ['MyTrait', Entity::TYPE_TRAIT],
                ['MyClass', Entity::TYPE_CLASS],
            ]
        );

        $entities['MyClass']->addUses($entities['MyTrait']);

        $traitUsedBy = $entities['MyTrait']->getUsedBy();
        $this->assertCount(1, $traitUsedBy);
        $this->assertArrayHasKey('MyClass', $traitUsedBy);
        $this->assertSame($entities['MyClass'], $traitUsedBy['MyClass']);
    }

    public function testAddUses_TraitUsesTrait_AppliesReverseLink()
    {
        $entities = $this->createEntities(
            [
                ['ParentTrait', Entity::TYPE_TRAIT],
                ['ChildTrait', Entity::TYPE_TRAIT],
            ]
        );

        $entities['ChildTrait']->addUses($entities['ParentTrait']);

        $parentTraitUsedBy = $entities['ParentTrait']->getUsedBy();
        $this->assertCount(1, $parentTraitUsedBy);
        $this->assertArrayHasKey('ChildTrait', $parentTraitUsedBy);
        $this->assertSame($entities['ChildTrait'], $parentTraitUsedBy['ChildTrait']);
    }

    public function testHasApiAncestor_NoApiAncestor_ReturnsFalse()
    {
        $entities = $this->createEntities(
            [
                ['MyTrait', Entity::TYPE_TRAIT, false],
                ['MyInterface', Entity::TYPE_INTERFACE, false],
                ['ParentClass', Entity::TYPE_CLASS, false],
                ['ChildClass', Entity::TYPE_CLASS, true],
            ]
        );

        //relate entities
        $entities['ChildClass']->addExtends($entities['ParentClass']);
        $entities['ChildClass']->addImplements($entities['MyInterface']);
        $entities['ChildClass']->addUses($entities['MyTrait']);

        //assert that method behaves as expected
        $this->assertFalse($entities['ChildClass']->hasApiAncestor());
    }

    public function testHasApiAncestor_ParentClassIsApi_ReturnsTrue()
    {
        $entities = $this->createEntities(
            [
                ['MyTrait', Entity::TYPE_TRAIT, false],
                ['MyInterface', Entity::TYPE_INTERFACE, false],
                ['ParentClass', Entity::TYPE_CLASS, true],
                ['ChildClass', Entity::TYPE_CLASS, true],
            ]
        );

        //relate entities
        $entities['ChildClass']->addExtends($entities['ParentClass']);
        $entities['ChildClass']->addImplements($entities['MyInterface']);
        $entities['ChildClass']->addUses($entities['MyTrait']);

        //assert that method behaves as expected
        $this->assertTrue($entities['ChildClass']->hasApiAncestor());
    }

    public function testHasApiAncestor_ParentClassHasApiAncestor_ReturnsTrue()
    {
        $entities = $this->createEntities(
            [
                ['MyTrait', Entity::TYPE_TRAIT, false],
                ['MyInterface', Entity::TYPE_INTERFACE, false],
                ['GrandParentClass', Entity::TYPE_CLASS, true],
                ['ParentClass', Entity::TYPE_CLASS, false],
                ['ChildClass', Entity::TYPE_CLASS, true],
            ]
        );

        //relate entities
        $entities['ChildClass']->addExtends($entities['ParentClass']);
        $entities['ChildClass']->addImplements($entities['MyInterface']);
        $entities['ChildClass']->addUses($entities['MyTrait']);
        $entities['ParentClass']->addExtends($entities['GrandParentClass']);

        //assert that method behaves as expected
        $this->assertTrue($entities['ChildClass']->hasApiAncestor());
    }

    public function testHasApiAncestor_ImplementedInterfaceIsApi_ReturnsTrue()
    {
        $entities = $this->createEntities(
            [
                ['MyTrait', Entity::TYPE_TRAIT, false],
                ['MyInterface', Entity::TYPE_INTERFACE, true],
                ['ParentClass', Entity::TYPE_CLASS, false],
                ['ChildClass', Entity::TYPE_CLASS, true],
            ]
        );

        //relate entities
        $entities['ChildClass']->addExtends($entities['ParentClass']);
        $entities['ChildClass']->addImplements($entities['MyInterface']);
        $entities['ChildClass']->addUses($entities['MyTrait']);

        //assert that method behaves as expected
        $this->assertTrue($entities['ChildClass']->hasApiAncestor());
    }

    public function testHasApiAncestor_ImplementedInterfaceHasApiAncestor_ReturnsTrue()
    {
        $entities = $this->createEntities(
            [
                ['MyTrait', Entity::TYPE_TRAIT, false],
                ['ParentInterface', Entity::TYPE_INTERFACE, true],
                ['MyInterface', Entity::TYPE_INTERFACE, false],
                ['ParentClass', Entity::TYPE_CLASS, false],
                ['ChildClass', Entity::TYPE_CLASS, true],
            ]
        );

        //relate entities
        $entities['ChildClass']->addExtends($entities['ParentClass']);
        $entities['ChildClass']->addImplements($entities['MyInterface']);
        $entities['ChildClass']->addUses($entities['MyTrait']);
        $entities['MyInterface']->addExtends($entities['ParentInterface']);

        //assert that method behaves as expected
        $this->assertTrue($entities['ChildClass']->hasApiAncestor());
    }

    public function testHasApiAncestor_UsedTraitIsApi_ReturnsTrue()
    {
        $entities = $this->createEntities(
            [
                ['MyTrait', Entity::TYPE_TRAIT, true],
                ['MyInterface', Entity::TYPE_INTERFACE, false],
                ['ParentClass', Entity::TYPE_CLASS, false],
                ['ChildClass', Entity::TYPE_CLASS, true],
            ]
        );

        //relate entities
        $entities['ChildClass']->addExtends($entities['ParentClass']);
        $entities['ChildClass']->addImplements($entities['MyInterface']);
        $entities['ChildClass']->addUses($entities['MyTrait']);

        //assert that method behaves as expected
        $this->assertTrue($entities['ChildClass']->hasApiAncestor());
    }

    public function testHasApiAncestor_UsedTraitHasApiAncestor_ReturnsTrue()
    {
        $entities = $this->createEntities(
            [
                ['ParentTrait', Entity::TYPE_TRAIT, true],
                ['MyTrait', Entity::TYPE_TRAIT, false],
                ['MyInterface', Entity::TYPE_INTERFACE, false],
                ['ParentClass', Entity::TYPE_CLASS, false],
                ['ChildClass', Entity::TYPE_CLASS, true],
            ]
        );

        //relate entities
        $entities['ChildClass']->addExtends($entities['ParentClass']);
        $entities['ChildClass']->addImplements($entities['MyInterface']);
        $entities['ChildClass']->addUses($entities['MyTrait']);
        $entities['MyTrait']->addUses($entities['ParentTrait']);

        //assert that method behaves as expected
        $this->assertTrue($entities['ChildClass']->hasApiAncestor());
    }

    public function testHasApiDescendant_NoApiDescendant_ReturnsFalse()
    {
        $entities = $this->createEntities(
            [
                ['MyTrait', Entity::TYPE_TRAIT, false],
                ['MyInterface', Entity::TYPE_INTERFACE, false],
                ['ParentClass', Entity::TYPE_CLASS, true],
                ['ChildClass', Entity::TYPE_CLASS, false],
            ]
        );

        //relate entities
        $entities['ChildClass']->addExtends($entities['ParentClass']);
        $entities['ChildClass']->addImplements($entities['MyInterface']);
        $entities['ChildClass']->addUses($entities['MyTrait']);

        //assert that method behaves as expected
        $this->assertFalse($entities['ParentClass']->hasApiDescendant());
    }

    public function testHasApiDescendant_ChildClassIsApi_ReturnsTrue()
    {
        $entities = $this->createEntities(
            [
                ['MyTrait', Entity::TYPE_TRAIT, false],
                ['MyInterface', Entity::TYPE_INTERFACE, false],
                ['ParentClass', Entity::TYPE_CLASS, true],
                ['ChildClass', Entity::TYPE_CLASS, true],
            ]
        );

        //relate entities
        $entities['ChildClass']->addExtends($entities['ParentClass']);
        $entities['ChildClass']->addImplements($entities['MyInterface']);
        $entities['ChildClass']->addUses($entities['MyTrait']);

        //assert that method behaves as expected
        $this->assertTrue($entities['ParentClass']->hasApiDescendant());
    }

    public function testHasApiDescendant_ChildClassHasApiDescendant_ReturnsTrue()
    {
        $entities = $this->createEntities(
            [
                ['MyTrait', Entity::TYPE_TRAIT, false],
                ['MyInterface', Entity::TYPE_INTERFACE, false],
                ['ParentClass', Entity::TYPE_CLASS, true],
                ['ChildClass', Entity::TYPE_CLASS, false],
                ['GrandChildClass', Entity::TYPE_CLASS, true],
            ]
        );

        //relate entities
        $entities['ChildClass']->addExtends($entities['ParentClass']);
        $entities['ChildClass']->addImplements($entities['MyInterface']);
        $entities['ChildClass']->addUses($entities['MyTrait']);
        $entities['GrandChildClass']->addExtends($entities['ChildClass']);

        //assert that method behaves as expected
        $this->assertTrue($entities['ParentClass']->hasApiDescendant());
    }

    public function testHasApiDescendant_ChildInterfaceIsApi_ReturnsTrue()
    {
        $entities = $this->createEntities(
            [
                ['MyInterface', Entity::TYPE_INTERFACE, false],
                ['ChildInterface', Entity::TYPE_INTERFACE, true],
            ]
        );

        //relate entities
        $entities['ChildInterface']->addExtends($entities['MyInterface']);

        //assert that method behaves as expected
        $this->assertTrue($entities['MyInterface']->hasApiDescendant());
    }

    public function testHasApiDescendant_ChildInterfaceHasApiDescendant_ReturnsTrue()
    {

        $entities = $this->createEntities(
            [
                ['MyInterface', Entity::TYPE_INTERFACE, false],
                ['ChildInterface', Entity::TYPE_INTERFACE, false],
                ['GrandChildInterface', Entity::TYPE_INTERFACE, true],
            ]
        );

        //relate entities
        $entities['ChildInterface']->addExtends($entities['MyInterface']);
        $entities['GrandChildInterface']->addExtends($entities['ChildInterface']);

        //assert that method behaves as expected
        $this->assertTrue($entities['MyInterface']->hasApiDescendant());
    }

    public function testHasApiDescendant_InterfaceIsImplementedByApiClass_ReturnsTrue()
    {
        $entities = $this->createEntities(
            [
                ['MyInterface', Entity::TYPE_INTERFACE, false],
                ['MyClass', Entity::TYPE_INTERFACE, true],
            ]
        );

        //relate entities
        $entities['MyClass']->addImplements($entities['MyInterface']);

        //assert that method behaves as expected
        $this->assertTrue($entities['MyInterface']->hasApiDescendant());
    }

    public function testHasApiDescendant_InterfaceIsImplementedByClassWithApiDescendant_ReturnsTrue()
    {
        $entities = $this->createEntities(
            [
                ['MyInterface', Entity::TYPE_INTERFACE, false],
                ['MyClass', Entity::TYPE_INTERFACE, false],
                ['ChildClass', Entity::TYPE_INTERFACE, true],
            ]
        );

        //relate entities
        $entities['MyClass']->addImplements($entities['MyInterface']);
        $entities['ChildClass']->addExtends($entities['MyClass']);

        //assert that method behaves as expected
        $this->assertTrue($entities['MyInterface']->hasApiDescendant());
    }

    public function testHasApiDescendant_ChildTraitIsApi_ReturnsTrue()
    {
        $entities = $this->createEntities(
            [
                ['MyTrait', Entity::TYPE_INTERFACE, false],
                ['ChildTrait', Entity::TYPE_INTERFACE, true],
            ]
        );

        //relate entities
        $entities['ChildTrait']->addUses($entities['MyTrait']);

        //assert that method behaves as expected
        $this->assertTrue($entities['MyTrait']->hasApiDescendant());
    }

    public function testHasApiDescendant_ChildTraitHasApiDescendant_ReturnsTrue()
    {
        $entities = $this->createEntities(
            [
                ['MyTrait', Entity::TYPE_INTERFACE, false],
                ['ChildTrait', Entity::TYPE_INTERFACE, false],
                ['GrandChildTrait', Entity::TYPE_INTERFACE, true],
            ]
        );

        //relate entities
        $entities['ChildTrait']->addUses($entities['MyTrait']);
        $entities['GrandChildTrait']->addUses($entities['ChildTrait']);

        //assert that method behaves as expected
        $this->assertTrue($entities['MyTrait']->hasApiDescendant());
    }

    public function testHasApiDescendant_TraitIsUsedByClassWithApiDescendant_ReturnsTrue()
    {
        $entities = $this->createEntities(
            [
                ['MyTrait', Entity::TYPE_INTERFACE, false],
                ['MyClass', Entity::TYPE_INTERFACE, false],
                ['ChildClass', Entity::TYPE_INTERFACE, true],
            ]
        );

        //relate entities
        $entities['MyClass']->addUses($entities['MyTrait']);
        $entities['ChildClass']->addExtends($entities['MyClass']);

        //assert that method behaves as expected
        $this->assertTrue($entities['MyTrait']->hasApiDescendant());
    }

    /**
     * @dataProvider dataProviderIsClass
     * @param string $type
     * @param bool $expected
     */
    public function testIsClass(string $type, bool $expected)
    {
        $entity = new Entity('myName', $type);

        $this->assertEquals(
            $expected,
            $entity->isClass()
        );
    }

    /**
     * @dataProvider dataProviderIsInterface
     * @param string $type
     * @param bool $expected
     */
    public function testIsInterface(string $type, bool $expected)
    {
        $entity = new Entity('myName', $type);

        $this->assertEquals(
            $expected,
            $entity->isInterface()
        );
    }

    /**
     * @dataProvider dataProviderIsTrait
     * @param string $type
     * @param bool $expected
     */
    public function testIsTrait(string $type, bool $expected)
    {
        $entity = new Entity('myName', $type);

        $this->assertEquals(
            $expected,
            $entity->isTrait()
        );
    }

    /*
     * Data providers
     */

    /**
     * Provides test data for {@link EntityTest::testIsClass()}
     *
     * @return array
     */
    public function dataProviderIsClass()
    {
        return [
            'entity-is-class-returns-true'      => [
                Entity::TYPE_CLASS,
                true,
            ],
            'entity-is-interface-returns-false' => [
                Entity::TYPE_INTERFACE,
                false,
            ],
            'entity-is-trait-returns-false'     => [
                Entity::TYPE_TRAIT,
                false,
            ],
        ];
    }

    /**
     * Provides test data for {@link EntityTest::testIsInterface()}
     *
     * @return array
     */
    public function dataProviderIsInterface()
    {
        return [
            'entity-is-class-returns-false'     => [
                Entity::TYPE_CLASS,
                false,
            ],
            'entity-is-interface-returns-false' => [
                Entity::TYPE_INTERFACE,
                true,
            ],
            'entity-is-trait-returns-false'     => [
                Entity::TYPE_TRAIT,
                false,
            ],
        ];
    }

    /**
     * Provides test data for {@link EntityTest::testIsTrait()}
     *
     * @return array
     */
    public function dataProviderIsTrait()
    {
        return [
            'entity-is-class-returns-false'     => [
                Entity::TYPE_CLASS,
                false,
            ],
            'entity-is-interface-returns-false' => [
                Entity::TYPE_INTERFACE,
                false,
            ],
            'entity-is-trait-returns-true'      => [
                Entity::TYPE_TRAIT,
                true,
            ],
        ];
    }

    /*
     * Private methods
     */

    /**
     * Creates instances of {@link Entity} as specified in `$data`.
     *
     * `$data` is expected to contain arrays. The content of those arrays evaluates as follows:
     * * At index `0` the name of the entity
     * * At index `1` the type of the entity (one of `Entity::TYPE_*`)
     * * At index `2` whether the entity shall have the API flag (optional, default: false)
     *
     * @param array $data
     * @return array<string, Entity>|Entity[]
     */
    private function createEntities(array $data): array
    {
        $entities = [];

        foreach ($data as $row) {
            $name   = $row[0];
            $type   = $row[1];
            $api    = (bool)($row[2] ?? false);
            $entity = new Entity($name, $type);

            $entity->setIsApi($api);
            $entities[$name] = $entity;
        }

        return $entities;
    }
}
