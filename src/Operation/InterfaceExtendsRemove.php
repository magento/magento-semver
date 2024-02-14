<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Operation;

use PhpParser\Node\Stmt;
use PHPSemVerChecker\Node\Statement\Interface_ as Interface_Statement;
use PHPSemVerChecker\Operation\Operation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * When extend is removed from marked api class.
 */
class InterfaceExtendsRemove extends Operation
{
    /**
     * Change level.
     *
     * @var int
     */
    protected $level = Level::MAJOR;

    /**
     * @var string
     */
    protected $code = 'M0122';

    /**
     * @var string
     */
    protected $reason = 'Extends has been removed.';
    /**
     * @var Stmt\Interface_
     */
    private $interface;

    /**
     * @param Stmt\Interface_ $interface
     * @param string $target
     */
    public function __construct(Stmt\Interface_ $interface, $target)
    {
        $this->target = $target;
        $this->interface = $interface;
    }

    /**
     * @return string
     */
    public function getTarget(): string
    {
        return Interface_Statement::getFullyQualifiedName($this->interface);
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->target;
    }

    /**
     * @return int
     */
    public function getLine(): int
    {
        return 0;
    }

    /**
     * Get level.
     *
     * @return mixed
     */
    public function getLevel(): int
    {
        return $this->level;
    }
}
