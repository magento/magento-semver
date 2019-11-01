<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionCheckr\Filter\AllowedChangeFilter;

class PhpWhitespaceFilter implements ChangedFileFilterInterface
{
    const TRIM_BLANK_LINES = 1;
    const TRIM_LEADING_WHITESPACE = 2;
    const TRIM_TRAILING_WHITESPACE = 4;
    const TRIM_ALL = 7;

    /** @var int */
    private $flags;

    /**
     * PhpWhitespaceFilter constructor.
     * @param int $trimTypeFlags
     * @return void
     */
    public function __construct($trimTypeFlags = self::TRIM_ALL)
    {
        $this->flags = $trimTypeFlags;
    }

    /**
     * Filters out *.php files that are identical if whitespace is trimmed
     *
     * @param array[] $beforeFileContents
     * @param array[] $afterFileContents
     * @return void
     */
    public function filter(&$beforeFileContents, &$afterFileContents)
    {
        $toCompare = array_filter(
            array_intersect(array_keys($beforeFileContents), array_keys($afterFileContents)),
            function ($path) { return pathinfo($path, PATHINFO_EXTENSION) == 'php'; }
        );

        foreach ($toCompare as $path) {
            $before = $beforeFileContents[$path];
            $after = $afterFileContents[$path];

            if ($this->flags & self::TRIM_LEADING_WHITESPACE) {
                $before = array_map('ltrim', $before);
                $after = array_map('ltrim', $after);
            }

            if ($this->flags & self::TRIM_TRAILING_WHITESPACE) {
                $before = array_map('rtrim', $before);
                $after = array_map('rtrim', $after);
            }

            if ($this->flags & self::TRIM_BLANK_LINES) {
                $before = array_values(array_filter($before));
                $after = array_values(array_filter($after));
            }

            if ($before == $after) {
                unset($beforeFileContents[$path]);
                unset($afterFileContents[$path]);
            } else {
                $beforeFileContents[$path] = $before;
                $afterFileContents[$path] = $after;
            }
        }
    }
}
