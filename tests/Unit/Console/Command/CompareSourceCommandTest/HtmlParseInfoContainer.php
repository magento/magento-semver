<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Test\Unit\Console\Command\CompareSourceCommandTest;

/**
 * Container for pattern and xpath used to validate SVC Report html
 */
class HtmlParseInfoContainer
{
    /**
     * @var string|null
     */
    public $xpath;

    /**
     * @var string|null
     */
    public $pattern;

    /**
     * HtmlParseInfoContainer constructor.
     * @param string|null $pattern
     * @param string|null $xpath
     */
    public function __construct(?string $pattern, ?string $xpath = null)
    {
        if (!($pattern || $xpath)) {
            throw new \InvalidArgumentException('$pattern and $xpath can not both be empty');
        }
        $this->xpath = $xpath;
        $this->pattern = $pattern;
    }
}
