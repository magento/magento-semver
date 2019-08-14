<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker;


use Magento\SemanticVersionChecker\Scanner\Scanner;
use PHPSemVerChecker\Registry\Registry;


class ScannerRegistry
{

    private $scanners = [];

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
     * @param string $filePath
     * @return array
     */
    private function getScannerByFile(string $filePath): array
    {
        $relevantScanner = [];
        foreach ($this->scanners as $scanner) {
            foreach ($scanner['pattern'] as $pattern) {
                if (preg_match('/^.'.$pattern.'$/', $filePath)) {
                    $relevantScanner[] = $scanner['scanner'];
                }
            }
        }

        return $relevantScanner;
    }

    /**
     * @return Registry[]
     */
    public function getPhpScannerRegistryList(): array
    {
        $registryList = [];
        foreach ($this->scanners as $key => $scanner) {
            if ($scanner['type'] !== 'php') {
                continue;
            }
            /** @var Scanner $scannerObject */
            $scannerObject = $scanner['scanner'];
            $registryList[$key] = $scannerObject->getRegistry();
        }

        return $registryList;
    }

    /**
     * @return Registry[]
     */
    public function getScannerRegistryList(): array
    {
        $registryList = [];
        foreach ($this->scanners as $key => $scanner) {
            /** @var Scanner $scannerObject */
            $scannerObject = $scanner['scanner'];
            $registryList[$key] = $scannerObject->getRegistry();
        }

        return $registryList;
    }
}
