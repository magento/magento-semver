<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Node;

class VirtualType
{
    /**
     * @var string
     */
    private $name;

    /*
     * @var string
     */
    private $scope;

    /**
     * @var bool
     */
    private $shared;

    /**
     * @var string
     */
    private $type;

    /**
     * @param string $name
     * @param string $scope
     * @param string $type
     * @param bool $shared
     */
    public function __construct(string $name, string $scope, string $type, bool $shared)
    {
        $this->name = $name;
        $this->scope = $scope;
        $this->shared = $shared;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isShared(): bool
    {
        return $this->shared;
    }

    /**
     * @return string
     */
    public function getScope(): string
    {
        return $this->scope;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
