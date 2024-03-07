<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Operation;

use PhpParser\Node\Stmt;
use PHPSemVerChecker\Operation\Operation;
use PHPSemVerChecker\Operation\Visibility;

/**
 * Abstract Class for visibility compare operation
 */
abstract class VisibilityOperation extends Operation
{
    /**
     * Error codes.
     *
     * @var array
     */
    protected $code = 'M109';

    /**
     * Operation message.
     *
     * @var string
     */
    protected $reason = 'Member visibility has been changed from %s to %s';

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
     * @var \PhpParser\Node\Stmt
     */
    protected $memberBefore;

    /**
     * @var string
     */
    protected $fileAfter;

    /**
     * @var \PhpParser\Node\Stmt
     */

    protected $contextAfter;

    /**
     * Property after changes.
     *
     * @var \PhpParser\Node\Stmt
     */
    protected $memberAfter;

    /**
     * @param string $context
     * @param string $fileBefore
     * @param Stmt   $contextBefore
     * @param Stmt   $memberBefore
     */
    public function __construct(
        $context,
        $fileBefore,
        Stmt $contextBefore,
        Stmt $memberBefore,
        $fileAfter,
        Stmt $contextAfter,
        Stmt $memberAfter
    ) {
        $this->context       = $context;
        $this->fileBefore    = $fileBefore;
        $this->contextBefore = $contextBefore;
        $this->memberBefore  = $memberBefore;
        $this->fileAfter     = $fileAfter;
        $this->contextAfter  = $contextAfter;
        $this->memberAfter   = $memberAfter;
    }

    /**
     * Get code.
     *
     * @return mixed
     */
    public function getCode(): string
    {
        return $this->code;
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
     * @return string
     */
    public function getTarget(): string
    {
        $namespace = $this->getMemberName($this->memberAfter);
        if (isset($this->contextAfter->namespacedName)) {
            $namespace = $this->contextAfter->namespacedName->toString() . '::' . $namespace;
        }
        return $namespace;
    }

    /**
     * Returns line position of existed member.
     *
     * @return int
     */
    public function getLine(): int
    {
        return $this->memberBefore->getLine();
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

    /**
     * @return string
     */
    public function getReason(): string
    {
        return sprintf(
            $this->reason,
            '[' . Visibility::toString(Visibility::getForContext($this->memberBefore)) . ']',
            '[' . Visibility::toString(Visibility::getForContext($this->memberAfter)) . ']'
        );
    }

    /**
     * Returns the name of the given class member
     *
     * @param $member
     *
     * @return string
     */
    abstract protected function getMemberName($member);
}
