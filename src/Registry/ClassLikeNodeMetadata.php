<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Registry;

use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\TraitUse;

/**
 * Class NodeMetadata
 * Container for various metadata about ClassLike node
 * @package Magento\SemanticVersionChecker\Registry
 */
class ClassLikeNodeMetadata
{
    /** @var TraitUse[] $traits */
    private $traits = [];

    /** @var int */
    private $hash = 0;

    /** @var array ClassMethods */
    private $methods = [];

    private $methodHashes = [];

    /**
     * @return TraitUse[]
     */
    public function getTraits(): array
    {
        return $this->traits;
    }

    /**
     * @param TraitUse[] $traits
     */
    public function setTraits(array $traits): void
    {
        $this->traits = $traits;
    }

    /**
     * @return int
     */
    public function getHash(): int
    {
        return $this->hash;
    }

    /**
     * @param Stmt\ClassMethod $method
     */
    private function setMethodHash(Stmt\ClassMethod $method): void
    {
        $hash = 0;
        if(!empty($method->stmts)) {
            $strMap = array_map(function (Node $stmt) { return (string)$stmt; }, $method->stmts);
            $hash = crc32(join($strMap));
        }
        $this->methodHashes[(string)$method->namespacedName] = $hash;
    }

    /**
     * @return array
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @param array $methods
     */
    public function setMethods(array $methods): void
    {
        $this->methods = [];
        $this->methodHashes = [];
        foreach ($methods as $method) {
            $this->addMethod($method);
        }
        $this->methods = $methods;
    }

    public function addMethod(Stmt\ClassMethod $method) {
        $this->setMethodHash($method);
        $method->stmts = [];
        $this->methods[(string)$method->namespacedName] = $method;
    }
}
