<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Filter\AllowedChangeFilter;

class PhpIgnoredTagFilter implements ChangedFileFilterInterface
{
    /** @var string */
    private $ignoredTagInlineMatcher;

    /** @var string */
    private $ignoredTagBlockMatcher;

    /** @var string */
    private $ignoredValInlineMatcher;

    /** @var string */
    private $ignoredValBlockMatcher;

    /** @var bool */
    private $caseSensitive;

    /** @var PhpCommentFormattingFilter */
    private $commentFilter;

    /**
     * @param string[] $ignoredTags
     * @param string[] $ignoredTagValues
     * @param bool $caseSensitive
     */
    public function __construct($ignoredTags, $ignoredTagValues, $caseSensitive = false)
    {
        $this->caseSensitive = $caseSensitive;

        $this->ignoredTagInlineMatcher = $this->getInlineTagMatcher($ignoredTags);
        $this->ignoredTagBlockMatcher = $this->getBlockTagMatcher($ignoredTags);

        $this->ignoredValInlineMatcher = $this->getInlineTagMatcher($ignoredTagValues);
        $this->ignoredValBlockMatcher = $this->getBlockTagMatcher($ignoredTagValues);

        $this->commentFilter = new PhpCommentFormattingFilter();
    }

    /**
     * Filters out *.php files that are identical if annotations are ignored
     *
     * @param array[] $beforeFileContents
     * @param array[] $afterFileContents
     * @return void
     */
    public function filter(&$beforeFileContents, &$afterFileContents)
    {
        // Normalize comments before checking for tags so inconsistent comment formatting isn't an issue
        $this->commentFilter->filter($beforeFileContents, $afterFileContents);

        $toCompare = array_filter(
            array_intersect(array_keys($beforeFileContents), array_keys($afterFileContents)),
            function ($path) {
                return pathinfo($path, PATHINFO_EXTENSION) == 'php';
            }
        );

        foreach ($toCompare as $path) {
            $beforeFiltered = $this->removeIgnoredTags($beforeFileContents[$path]);
            $afterFiltered = $this->removeIgnoredTags($afterFileContents[$path]);
            if ($beforeFiltered == $afterFiltered) {
                unset($beforeFileContents[$path]);
                unset($afterFileContents[$path]);
            } else {
                $beforeFileContents[$path] = $beforeFiltered;
                $afterFileContents[$path] = $afterFiltered;
            }
        }

        // Normalize comment formatting again after ignored tags have been removed to clear any now-empty comments
        $this->commentFilter->filter($beforeFileContents, $afterFileContents);
    }

    /**
     * Get the contents of the file after removing ignored tags and truncating ignored values
     *
     * @param string[] $lines
     * @return string[]
     */
    private function removeIgnoredTags($lines)
    {
        $newLines = [];
        foreach ($lines as $line) {
            if (preg_match($this->ignoredValBlockMatcher, $line, $matches)) {
                $newLine = $matches[1] . '* @' . $matches[2];
                $newLines[] = $this->caseSensitive ? $newLine : strtolower($newLine);
            } elseif (preg_match($this->ignoredValInlineMatcher, $line, $matches)) {
                $newLine = $matches[1] . '/** @' . $matches[2] . ' */';
                $newLines[] = $this->caseSensitive ? $newLine : strtolower($newLine);
            } elseif (
                preg_match($this->ignoredTagBlockMatcher, $line) !== 1
                && preg_match($this->ignoredTagInlineMatcher, $line) !== 1
            ) {
                $newLines[] = $line;
            }
        }
        return $newLines;
    }

    /**
     * Constructs a regex matcher for single-line block comments that start with one of the given tag annotations
     *
     * Tags can only be followed by whitespace, open paren, or end of comment block
     *
     * @param string[] $tags
     * @return string
     */
    private function getInlineTagMatcher($tags)
    {
        $tagMatcher = implode('|', array_map('preg_quote', $tags));
        $matcher = "#^(\s*)\/\*\*\s@($tagMatcher)((\s|\().*)?\s\*\/$#";
        if (!$this->caseSensitive) {
            $matcher = $matcher . 'i';
        }
        return $matcher;
    }

    /**
     * Constructs a regex matcher for mid-comment block lines that start with one of the given tag annotations
     *
     * Tags can only be followed by whitespace, open paren, or end of line
     *
     * @param string[] $tags
     * @return string
     */
    private function getBlockTagMatcher($tags)
    {
        $tagMatcher = implode('|', array_map('preg_quote', $tags));
        $matcher = "#^(\s*)\*\s@($tagMatcher)(?=$|\s|\()#";
        if (!$this->caseSensitive) {
            $matcher = $matcher . 'i';
        }
        return $matcher;
    }
}
