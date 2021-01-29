<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Helper;

use Symfony\Component\Console\Input\InputInterface;

/**
 * Determines the corresponding package name for a file
 */
class PackageNameResolver
{
    /**
     * @var InputInterface $input
     */
    private $input;

    /**
     * PackageNameResolver constructor.
     *
     * @param InputInterface|null $input
     */
    public function __construct(InputInterface $input)
    {
        $this->input = $input;
    }

    /**
     * Gets the matching composer.json given a filepath. Will return null if composer.json is not found
     *
     * @param string $filepath
     * @return string|null
     */
    private function getComposerPackageLocation(string $filepath): ?string
    {
        $sourceBeforeDir = realpath($this->input->getArgument('source-before'));
        $sourceAfterDir = realpath($this->input->getArgument('source-after'));
        $level = 1;
        $composerDirPath = dirname($filepath, $level);
        while (
            $composerDirPath !== $sourceBeforeDir
            && $composerDirPath !== $sourceAfterDir
            && $composerDirPath !== '.'
        ) {
            $composerPath = $composerDirPath . '/composer.json';
            if (is_file($composerPath)) {
                return $composerPath;
            }
            $composerDirPath = dirname($filepath, ++$level);
        }
        return null;
    }

    /**
     * Get the real name of package that contains the input file
     *
     * @param string $filepath
     * @return string|null
     */
    public function getPackageName(string $filepath): ?string
    {
        $composerFilePath = $this->getComposerPackageLocation($filepath);
        if (!$composerFilePath) {
            return null;
        }
        $composerFile = file_get_contents($composerFilePath);
        $composerJson = json_decode($composerFile);
        return $composerJson->name;
    }
}
