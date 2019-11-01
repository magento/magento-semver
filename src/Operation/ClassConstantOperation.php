<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionCheckr\Operation;

use PHPSemVerChecker\Operation\Operation;

abstract class ClassConstantOperation extends Operation
{
    /**
     * @var string
     */
    protected $context;

    /**
     * Get code.
     *
     * @return mixed
     */
    public function getCode()
    {
        return $this->code[$this->context];
    }

    /**
     * Get level.
     *
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level[$this->context];
    }
}
