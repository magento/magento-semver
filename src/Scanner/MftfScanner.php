<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Scanner;

use Magento\SemanticVersionChecker\Registry\XmlRegistry;
use PHPSemVerChecker\Registry\Registry;

/**
 * Class MftfScanner
 */
class MftfScanner implements ScannerInterface
{
    const MFTF_ENTITY = 'mftfEntity';

    /**
     * @var XmlRegistry
     */
    private $registry;

    /**
     * @var ModuleNamespaceResolver
     */
    private $moduleNamespaceResolver;

    /**
     * MftfScanner constructor
     *
     * @param XmlRegistry $registry
     * @param ModuleNamespaceResolver $moduleNamespaceResolver
     */
    public function __construct(XmlRegistry $registry, ModuleNamespaceResolver $moduleNamespaceResolver)
    {
        $this->registry = $registry;
        $this->moduleNamespaceResolver = $moduleNamespaceResolver;
    }

    /**
     * Scans file
     *
     * @param string $file
     * @return void
     * @throws \Exception
     */
    public function scan($file): void
    {
        // Set the current file used by the registry so that we can tell where the change was scanned.
        $this->registry->setCurrentFile($file);
        $service = new \Sabre\Xml\Service();
        $xml = $service->parse(file_get_contents($file));
        $xmlResult = json_decode(json_encode($xml), true);
        foreach ($xmlResult as $entityNode) {
            $this->registerEntityNode($entityNode);
        }
    }

    /**
     * Returns the path of <var>$file</var> relative to <var>$module</var>.
     *
     * @param string $file
     * @param string $module
     * @return string
     */
    private function getRelativePath(string $file, string $module): string
    {
        $moduleSubPath         = implode('/', explode('_', $module));
        $moduleSubPathPosition = strpos($file, $moduleSubPath);

        return substr($file, $moduleSubPathPosition + strlen($moduleSubPath));
    }

    /**
     * Registers entity node in registry.
     *
     * @param array $entityNode
     * @return void
     */
    private function registerEntityNode(array $entityNode) :void
    {
        $name             = $entityNode['attributes']['name'];
        $file             = $this->registry->getCurrentFile();
        $moduleName       = $this->moduleNamespaceResolver->resolveByTestMftfPath($file);
        $relativeFilePath = $this->getRelativePath($file, $moduleName);
        $entityNode['filePaths'][] = $relativeFilePath;
        // trim {}test => test
        $entityNode['type'] = str_replace(['{', '}'], '', $entityNode['name']);

        $nodeExists = $this->registry->data[self::MFTF_ENTITY][$moduleName][$name] ?? null;
        if ($nodeExists !== null) {
            $this->getRegistry()->data[self::MFTF_ENTITY][$moduleName][$name]['value'] =
                array_merge_recursive($nodeExists['value'], $entityNode['value']);
            $this->getRegistry()->data[self::MFTF_ENTITY][$moduleName][$name]['filePaths'] =
                array_merge_recursive($nodeExists['filePaths'], $entityNode['filePaths']);
            $this->getRegistry()->data[self::MFTF_ENTITY][$moduleName][$name]['attributes'] =
                array_merge($nodeExists['attributes'], $entityNode['attributes']);
        } else {
            $this->getRegistry()->data[self::MFTF_ENTITY][$moduleName][$name] = $entityNode;
        }
    }

    /**
     * @return XmlRegistry
     */
    public function getRegistry(): Registry
    {
        return $this->registry;
    }
}
