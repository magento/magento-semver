<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Filter\AllowedChangeFilter;

class PhpCommentFormattingFilter implements ChangedFileFilterInterface
{
    /**
     * Filters out *.php files that are identical if blank comments are removed and comment formatting is normalized
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
            $before = $this->normalizeComments($beforeFileContents[$path]);
            $after = $this->normalizeComments($afterFileContents[$path]);

            if ($before == $after) {
                unset($beforeFileContents[$path]);
                unset($afterFileContents[$path]);
            } else {
                $beforeFileContents[$path] = $before;
                $afterFileContents[$path] = $after;
            }
        }
    }

    /**
     * Normalize block comment formatting
     *
     * Trims extra whitespace around comment tags, removes empty comments, condenses block comments with only one line
     * of content into a single line, and matches indentation to the next line of non-comment code
     *
     * @param string[] $lines
     * @return string[]
     */
    private function normalizeComments($lines)
    {
        $normalized = [];

        // Comment indentation should match the line of code after the comment, so iterate backwards
        $reversedLines = array_reverse($lines);

        $inBlockComment = false;
        $indentation = '';
        $blockComment = [];
        foreach ($reversedLines as $line) {
            $line = $this->trimCommentTags($line);
            if ($line !== false) {
                if (!$inBlockComment) {
                    if ($line == '*/') {
                        $inBlockComment = true;
                    } elseif (preg_match('#^\/\/#', $line) || preg_match('#^\/\*\*.*\*\/$#', $line)) {
                        $normalized[] = $indentation . $line;
                    } else {
                        // Non-comment line
                        if (trim($line)) {
                            // Track indentation for comment normalization
                            preg_match('#^\s*#', $line, $matches);
                            $indentation = $matches[0];
                        }
                        $normalized[] = $line;
                    }
                } else {
                    if ($line == '/**') {
                        $normalized = array_merge($normalized, $this->formatBlock($blockComment, $indentation));
                        $blockComment = [];
                        $inBlockComment = false;
                    } else {
                        $blockComment[] = preg_replace('#^\s*\*?\s*(.*?)\s*$#', '\1', $line);
                    }
                }
            }
        }
        return array_reverse($normalized);
    }

    /**
     * Normalize whitespace around comment tags and trim empty comment lines
     *
     * Returns false if it's an empty comment line
     *
     * @param string $line
     * @return string|bool
     */
    private function trimCommentTags($line)
    {
        if (preg_match('#^\s*\/\/\s*(.*)#', $line, $matches)) {
            $line = '//' . ($matches[1] ? ' ' . $matches[1] : '');
        } else {
            if (preg_match("#^\s*\/\*\*\s*(.*)#", $line, $matches)) {
                $line = '/**' . ($matches[1] ? ' ' . $matches[1] : '');
            }

            if (preg_match("#^(.*?)\s*\*\/\s*$#", $line, $matches)) {
                $line = ($matches[1] ? $matches[1] . ' ' : '') . '*/';
            }
        }

        if ($line == '/** */' || $line == '//') {
            return false;
        }
        return $line;
    }

    /**
     * Properly indents, condenses single-line, and removes empty block comments
     *
     * @param string[] $commentLines
     * @param string $indentation
     * @return string[]
     */
    private function formatBlock($commentLines, $indentation)
    {
        $commentLines = array_values(array_filter($commentLines));
        if (!$commentLines) return [];

        $formatted = [];
        if (count($commentLines) == 1) {
            $formatted[] = $indentation . '/** ' . $commentLines[0] . ' */';
        } else {
            $formatted[] = $indentation . ' */';
            foreach ($commentLines as $line) {
                $formatted[] = $indentation . ' * ' . $line;
            }
            $formatted[] = $indentation . '/**';
        }

        return $formatted;
    }
}
