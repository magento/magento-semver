<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Operation\EtSchema;

use PHPSemVerChecker\Operation\Operation;

/**
 * Class EtSchemaOperation
 */
class EtSchemaOperation extends Operation
{
    /**
     * @var string
     */
    private $location;

    /**
     * @var int
     */
    private $level;

    /**
     * @param string $location
     * @param string $code
     * @param string $target
     * @param string $reason
     * @param int $level
     */
    public function __construct(
        string $location,
        string $code,
        string $target,
        string $reason,
        int $level
    ) {
        $this->location = $location;
        $this->target = $target;
        $this->code = $code;
        $this->reason = $reason;
        $this->level = $level;
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @return int|string
     */
    public function getLine(): int
    {
        return 0;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }
}
