<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionCheckr\Filter\AllowedChangeFilter;

class ComposerVersionFilter implements ChangedFileFilterInterface
{
    /** @var string[] */
    private $sections;

    /** @var string[] */
    private $versionMatchers;

    /** @var string[] */
    private $pkgMatchers;

    /**
     * @param string[] $versionedSections
     * @param string[] $versionMatchers
     * @param string[] $pkgMatchers
     */
    public function __construct($versionedSections, $versionMatchers = ['#^\*$#'], $pkgMatchers = [])
    {
        $this->sections = $versionedSections;
        $this->versionMatchers = $versionMatchers;
        $this->pkgMatchers = $pkgMatchers;
    }

    /**
     * Filters out composer.json files that are identical other than allowed version constraints in the 'after' source
     *
     * @param array[] $beforeFileContents
     * @param array[] $afterFileContents
     * @return void
     */
    public function filter(&$beforeFileContents, &$afterFileContents)
    {
        $toCompare = array_filter(
            array_intersect(array_keys($beforeFileContents), array_keys($afterFileContents)),
            function ($path) { return pathinfo($path, PATHINFO_BASENAME) == 'composer.json'; }
        );

        foreach ($toCompare as $path) {
            $beforeLines = $beforeFileContents[$path];
            $afterLines = $afterFileContents[$path];
            $before = json_decode(implode(PHP_EOL, $beforeLines), true);
            $after = json_decode(implode(PHP_EOL, $afterLines), true);

            foreach ($this->sections as $section) {
                $after = $this->updateAfterSection($before, $after, $section);
            }

            if ($before == $after) {
                // ignoring constraints made the json match, so the files should be filtered out
                unset($beforeFileContents[$path]);
                unset($afterFileContents[$path]);
            } else {
                // re-encode the updated Json with ignored constraints reset to the before state for the next filter
                $afterFileContents[$path] = explode(PHP_EOL,
                    json_encode($after, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            }
        }
    }

    /**
     * If the constraints in the given section match the allowed constraints, update them to match the before values
     *
     * @param array $before
     * @param array $after
     * @param string $section
     * @return array
     */
    private function updateAfterSection($before, $after, $section)
    {
        if ($section == 'version') {
            if (
                key_exists($section, $before)
                && key_exists('name', $after) && $this->hasMatch($after['name'], $this->pkgMatchers)
                && (!key_exists($section, $after) || $this->hasMatch($after[$section], $this->versionMatchers))
            ) {
                $after[$section] = $before[$section];
            }
        } elseif (key_exists($section, $before) && key_exists($section, $after)) {
            $after[$section] = $this->resetIgnoredConstraints($before[$section], $after[$section]);
        }

        return $after;
    }

    /**
     * Set matching constraints in the after package to values from the before package
     *
     * @param array $before
     * @param array $after
     * @return array
     */
    private function resetIgnoredConstraints($before, $after)
    {
        $toIgnore = array_filter($after, function($constraint, $pkg) {
            return $this->shouldIgnore($pkg, $constraint);
        }, ARRAY_FILTER_USE_BOTH);

        foreach (array_keys($toIgnore) as $pkg) {
            if (key_exists($pkg, $before)) {
                $after[$pkg] = $before[$pkg];
            }
        }

        return $after;
    }

    /**
     * Helper function to check if a package name + constraint pair needs to be ignored
     *
     * @param string $pkg
     * @param string $constraint
     * @return bool
     */
    private function shouldIgnore($pkg, $constraint)
    {
        $pkgMatches = $this->hasMatch($pkg, $this->pkgMatchers);
        $constraintMatches = $this->hasMatch($constraint, $this->versionMatchers);
        return $pkgMatches && $constraintMatches;
    }

    /**
     * Helper function to check if a value fits any of the given regex matchers
     *
     * @param string $value
     * @param string[] $matchers
     * @return bool
     */
    private function hasMatch($value, $matchers)
    {
        if (!$matchers) {
            return true;
        }

        foreach ($matchers as $matcher) {
            if (preg_match($matcher, $value)) {
                return true;
            }
        }

        return false;
    }
}
