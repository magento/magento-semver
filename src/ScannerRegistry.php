<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker;

use Finder\FilenameMatch;
use Magento\SemanticVersionChecker\Scanner\ScannerInterface;
use PHPSemVerChecker\Registry\Registry;

/**
 * Registry for Scanner
 */
class ScannerRegistry
{
    /**
     * @var array
     */
    private $scanners;

    /**
     * @param array $scanners
     */
    public function __construct(array $scanners)
    {
        $this->scanners = $scanners;
    }

    /**
     * Fill the registry for registered scanner.
     *
     * @param string $filePath
     */
    public function scanFile(string $filePath): void
    {
        foreach ($this->getScannerByFile($filePath) as $scanner) {
            $scanner->scan($filePath);
        }
    }

    /**
     * Get all relevant scanner by given file path.
     *
     * @param string $filePath
     * @return ScannerInterface[]
     */
    private function getScannerByFile(string $filePath): array
    {
        $relevantScanner = [];
        foreach ($this->scanners as $scanner) {
            foreach ($scanner['pattern'] as $pattern) {
                $match = preg_match('/' . FilenameMatch::translate($pattern) . '/S', $filePath);
                if ($match !== false && $match !== 0) {
                    $relevantScanner[] = $scanner['scanner'];
                }
            }
        }

        return $relevantScanner;
    }

    /**
     * Get all registry as list with scanner key.
     *
     * @return Registry[]
     */
    public function getScannerRegistryList(): array
    {
        $registryList = [];
        foreach ($this->scanners as $key => $scanner) {
            /** @var ScannerInterface $scannerObject */
            $scannerObject = $scanner['scanner'];
            $registryList[$key] = $scannerObject->getRegistry();
        }

        return $registryList;
    }
}
