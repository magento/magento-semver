<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\SemanticVersionChecker\Test\Unit\Console\Command\CompareSourceCommandTest;

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

    const checkOnlyFirstXPathMatch = 0;
    const checkAllXpathMatches = 1;

    /**
     * @var bool
     */
    public $isRegex;

    /**
     * @var int
     */
    public $xpathSearchType;

    public function __construct(?string $pattern,
                                ?string $xpath = null,
                                bool $isRegex = false,
                                int $xpathSearchType = self::checkOnlyFirstXPathMatch)
    {
        $this->xpath = $xpath;
        $this->pattern = $pattern;
        $this->isRegex = $isRegex;
        $this->xpathSearchType = $xpathSearchType;
    }

}