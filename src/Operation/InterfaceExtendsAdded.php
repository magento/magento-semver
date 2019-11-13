<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Operation;

use PhpParser\Node\Stmt\Interface_;
use PHPSemVerChecker\Node\Statement\Interface_ as Interface_Statement;
use PHPSemVerChecker\Operation\Operation;
use PHPSemVerChecker\SemanticVersioning\Level;

/**
 * When extend is added to marked api interface.
 */
class InterfaceExtendsAdded extends Operation
{
    /**
     * Change level.
     *
     * @var int
     */
    protected $level = Level::MINOR;

    /**
     * @var string
     */
    protected $code = 'M0127';

    /**
     * @var string
     */
    protected $reason = 'Added parent to interface.';

    /**
     * @var Interface_
     */
    private $interface;

    /**
     * @param Interface_ $interface
     * @param string $target
     */
    public function __construct(Interface_ $interface, $target)
    {
        $this->target = $target;
        $this->interface = $interface;
    }

    /**
     * @return string
     */
    public function getTarget()
    {
        return Interface_Statement::getFullyQualifiedName($this->interface);
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->target;
    }

    /**
     * @return int
     */
    public function getLine()
    {
        return 0;
    }

    /**
     * Get level.
     *
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level;
    }
}
