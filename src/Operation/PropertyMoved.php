<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Operation;

use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Property;
use PHPSemVerChecker\Node\Statement\Property as PProperty;
use PHPSemVerChecker\Operation\PropertyOperation;
use PHPSemVerChecker\SemanticVersioning\Level;

class PropertyMoved extends PropertyOperation
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = [
        'class' => ['M093', 'M094'],
    ];

    /**
     * Error levels.
     *
     * @var array
     */
    protected $level = [
        'class'     => Level::PATCH,
        'interface' => Level::PATCH,
    ];

    /**
     * Operation message.
     *
     * @var string
     */
    protected $reason = 'Property has been moved to parent class.';

    /**
     * File path before changes.
     *
     * @var string
     */
    protected $fileBefore;

    /**
     * Property context before changes.
     *
     * @var \PhpParser\Node\Stmt
     */
    protected $contextBefore;

    /**
     * Property before changes.
     *
     * @var \PhpParser\Node\Stmt\Property
     */
    protected $propertyBefore;

    /**
     * @param string $context
     * @param string $fileBefore
     * @param \PhpParser\Node\Stmt $contextBefore
     * @param \PhpParser\Node\Stmt\Property $propertyBefore
     */
    public function __construct($context, $fileBefore, Stmt $contextBefore, Property $propertyBefore)
    {
        $this->context = $context;
        $this->visibility = $this->getVisibility($propertyBefore);
        $this->fileBefore = $fileBefore;
        $this->contextBefore = $contextBefore;
        $this->propertyBefore = $propertyBefore;
    }

    /**
     * Returns file path before changes.
     *
     * @return string
     */
    public function getLocation(): string
    {
        return $this->fileBefore;
    }

    /**
     * Returns line position of existed property.
     *
     * @return int
     */
    public function getLine(): int
    {
        return $this->propertyBefore->getLine();
    }

    /**
     * Returns fully qualified name of property.
     *
     * @return string
     */
    public function getTarget(): string
    {
        return PProperty::getFullyQualifiedName($this->contextBefore, $this->propertyBefore);
    }

    /**
     * Returns level of error.
     *
     * @return mixed
     */
    public function getLevel(): int
    {
        return $this->level[$this->context];
    }
}
