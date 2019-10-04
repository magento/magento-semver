<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tools\SemanticVersionChecker\Scanner;

use Magento\Framework\Convert\Xml;
use Magento\Tools\SemanticVersionChecker\Registry\XmlRegistry;
use PHPSemVerChecker\Registry\Registry;

/**
 * Class ScannerDecorator
 * @package Magento\Tools\SemanticVersionChecker\Scanner
 */
class DbSchemaScanner implements ScannerInterface
{

    /**
     * A list of contexts with all the nodes that were found in the source code.
     *
     * @var array
     */
    private $data;

    /**
     * @var XmlRegistry
     */
    private $registry;

    /**
     * @var ModuleNamespaceResolver
     */
    private $getModuleNameByPath;


    public function __construct(XmlRegistry $registry, ModuleNamespaceResolver $getModuleNameByPath)
    {
        $this->registry = $registry;
        $this->getModuleNameByPath = $getModuleNameByPath;
    }

    /**
     * @param string $file
     * @return void
     */
    public function scan(string $file): void
    {
        // Set the current file used by the registry so that we can tell where the change was scanned.
        $this->registry->setCurrentFile($file);

        if (!$this->isXml($file)) {
            return;
        }

        $xml = simplexml_load_string(file_get_contents($file));
        $xmlResult = json_decode(json_encode($xml), true);

        if (isset($xmlResult['table']['@attributes'])) {
            $this->processTable($xmlResult['table']);
        } else {
            foreach ($xmlResult['table'] as $tableData) {
                $this->processTable($tableData);
            }
        }
    }

    /**
     * @param string $file
     * @return bool
     */
    private function isXml(string $file): bool
    {
        return (bool)preg_match('/^.*.xml$/', $file);
    }

    /**
     * @param array $data
     */
    private function processTable(array $data)
    {
        $name = $data['@attributes']['name'];
        $moduleName = $this->getModuleNameByPath->resolveByEtcDirFilePath($this->registry->getCurrentFile());
        $resource = isset($data['@attributes']['resource']) ? $data['@attributes']['resource'] : 'default';
        $this->getRegistry()->data['table'][$moduleName][$name]['resource'] = $resource;

        $types = ['column', 'constraint', 'index'];
        foreach ($types as $type) {
            if (isset($data[$type])) {
                if (isset($data[$type]['@attributes'])) {
                    $this->registerObject($name, $type, $data[$type]['@attributes'], $moduleName);
                } else {
                    foreach ($data[$type] as $typeData) {
                        if (isset($typeData['@attributes'])) {
                            $this->registerObject($name, $type, $typeData['@attributes'], $moduleName);
                        }
                    }
                }
            }
        }
    }

    /**
     * @return XmlRegistry
     */
    public function getRegistry(): Registry
    {
        return $this->registry;
    }

    /**
     * @param string $name
     * @param string $type
     * @param array $typeData
     */
    private function registerObject($name, $type, array $typeData, $moduleName)
    {
        $typeName = $typeData['name'] ?? $typeData['referenceId'];
        $isForeignKey = isset($typeData['referenceTable']) && isset($typeData['referenceColumn']);
        if ($isForeignKey) {
            $type = 'foreign';
        } elseif ($type === 'index' || $type === 'constraint') {
            $type = 'key';
        }

        $this->getRegistry()->data['table'][$moduleName][$name][$type][$typeName] = $typeName;
    }
}
