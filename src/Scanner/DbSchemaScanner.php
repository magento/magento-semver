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
 * Class ScannerDecorator
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
     * Types of files that allowed to parse
     *
     * @var array
     */
    private $allowedTypes = [
        'xml',
        'json'
    ];

    /**
     * @var XmlRegistry
     */
    private $registry;

    /**
     * @var ModuleNamespaceResolver
     */
    private $getModuleNameByPath;

    /**
     * DbSchemaScanner constructor
     *
     * @param XmlRegistry $registry
     * @param ModuleNamespaceResolver $getModuleNameByPath
     */
    public function __construct(XmlRegistry $registry, ModuleNamespaceResolver $getModuleNameByPath)
    {
        $this->registry = $registry;
        $this->getModuleNameByPath = $getModuleNameByPath;
    }

    /**
     * Scans file
     *
     * @param string $file
     * @return void
     */
    public function scan(string $file): void
    {
        // Set the current file used by the registry so that we can tell where the change was scanned.
        $this->registry->setCurrentFile($file);

        if (!$this->isAllowedToPars($file)) {
            return;
        }
        if ($this->isXml($file)) {
            $this->scanXml($file);
        }
        if ($this->isJson($file)) {
            $this->scanJson($file);
        }
    }

    /**
     * Scans XML
     *
     * @param string $filePath
     */
    public function scanXml(string $filePath): void
    {
        $xml = simplexml_load_string(file_get_contents($filePath));
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
     * Scans JSON
     *
     * @param string $filePath
     */
    public function scanJson(string $filePath): void
    {
        $tables = json_decode(file_get_contents($filePath), true);
        $moduleName = $this->getModuleNameByPath->resolveByEtcDirFilePath($this->registry->getCurrentFile());
        $this->getRegistry()->mapping['whitelist_json'][$moduleName] = $filePath;
        foreach ($tables as $tableName => $tableData) {
            $this->getRegistry()->data['whitelist_json'][$moduleName][$tableName] = $tableData;
        }
    }

    /**
     * Checks if file can be parsed
     *
     * @param string $file
     *
     * @return bool
     */
    private function isAllowedToPars(string $file): bool
    {
        return (bool)preg_match('/^.*.(' . implode('|', $this->allowedTypes) . ')/', $file);
    }

    /**
     * Checks if file has XML extension
     *
     * @param string $file
     *
     * @return bool
     */
    private function isXml(string $file): bool
    {
        return (bool)preg_match('/^.*.xml$/', $file);
    }

    /**
     * Checks if file has JSON extension
     *
     * @param string $file
     *
     * @return bool
     */
    private function isJson(string $file): bool
    {
        return (bool)preg_match('/^.*.json/', $file);
    }

    /**
     * Processes table
     *
     * @param array $data
     */
    private function processTable(array $data)
    {
        $name = $data['@attributes']['name'];
        $file = $this->getRegistry()->getCurrentFile();
        $moduleName = $this->getModuleNameByPath->resolveByEtcDirFilePath($file);
        $this->getRegistry()->mapping['table'][$moduleName] = $file;
        $resource = isset($data['@attributes']['resource']) ? $data['@attributes']['resource'] : 'default';
        $this->getRegistry()->data['table'][$moduleName][$name]['resource'] = $resource;

        $types = ['column', 'constraint', 'index'];
        foreach ($types as $type) {
            if (isset($data[$type])) {
                if (isset($data[$type]['@attributes'])) {
                    $this->registerObject($name, $type, $data[$type], $moduleName);
                } else {
                    foreach ($data[$type] as $typeData) {
                        if (isset($typeData['@attributes'])) {
                            $this->registerObject($name, $type, $typeData, $moduleName);
                        }
                    }
                }
            }
        }
    }

    /**
     * Provides registry
     *
     * @return XmlRegistry
     */
    public function getRegistry(): Registry
    {
        return $this->registry;
    }

    /**
     * Registers object
     *
     * @param string $name
     * @param string $type
     * @param array $typeData
     * @param string $moduleName
     */
    private function registerObject($name, $type, array $typeData, string $moduleName)
    {
        $typeName = $typeData['@attributes']['name'] ?? $typeData['@attributes']['referenceId'];
        $typeInfo = $typeName;
        if (isset($typeData['@attributes']['referenceTable']) && isset($typeData['@attributes']['referenceColumn'])) {
            $type = 'foreign';
            $typeInfo = [];
            $typeInfo['referenceId'] = $typeData['@attributes']['referenceId'];
            $typeInfo['table'] = $typeData['@attributes']['table'];
            $typeInfo['column'] = $typeData['@attributes']['column'];
            $typeInfo['referenceTable'] = $typeData['@attributes']['referenceTable'];
            $typeInfo['referenceColumn'] = $typeData['@attributes']['referenceColumn'];
            $typeInfo['onDelete'] = $typeData['@attributes']['onDelete'];
        } elseif ($type === 'index') {
            $typeInfo = [];
            $type = 'index';
            $typeInfo['indexType'] = isset($typeData['@attributes']['indexType']) ?? null;
            $typeInfo['referenceId'] = $typeData['@attributes']['referenceId'];
            $typeInfo['columns'] = $this->getIndexColumns($typeData);
        } elseif ($type === 'constraint' && $typeData['@attributes']['referenceId'] === 'PRIMARY') {
            $type = 'primary';
            $typeInfo = [];
            $typeInfo['columns'] = $this->getIndexColumns($typeData);
        } elseif ($type === 'constraint') {
            $type = 'unique';
            $typeInfo = [];
            $typeInfo['referenceId'] = $typeData['@attributes']['referenceId'];
            $typeInfo['columns'] = $this->getIndexColumns($typeData);
        }

        $this->getRegistry()->data['table'][$moduleName][$name][$type][$typeName] = $typeInfo;
    }

    /**
     * Extracts columns info
     *
     * @param array $typeData
     *
     * @return array
     */
    private function getIndexColumns(array $typeData): array
    {
        $columnsArray = [];
        if (!isset($typeData['column'])) {
            return  $columnsArray;
        }
        if (isset($typeData['column']['@attributes'])) {
            $columnsArray[] = $typeData['column']['@attributes']['name'];
        } else {
            foreach ($typeData['column'] as $column) {
                if (isset($column['@attributes'])) {
                    $columnsArray[] = $column['@attributes']['name'];
                }
            }
        }

        return $columnsArray;
    }
}
