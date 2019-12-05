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

    const DB_SCHEMA_XML =  'db_schema.xml';
    const DB_SCHEMA_WHITELIST_JSON =  'db_schema_whitelist.json';

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
        if ($this->isXml($file) && !$this->isXmlAlreadyScanned($file)) {
            $this->scanXml($file);
            $this->associateJsonWithProvidedXmlPath($file);
        }
        if ($this->isJson($file) && !$this->isJsonAlreadyScanned($file)) {
            $this->scanJson($file);
            $this->associateXmlWithProvidedJsonPath($file);
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
        foreach ($tables as $tableName => $tableData) {
            $this->getRegistry()->data['whitelist_json'][$moduleName][$tableName] = $tableData;
        }
    }

    /**
     * Checks if xml file has already been scanned.
     *
     * @param string $xmlFilePath
     * @return bool
     */
    private function isXmlAlreadyScanned(string $xmlFilePath): bool
    {
        $moduleName = $this->getModuleNameByPath->resolveByEtcDirFilePath($xmlFilePath);
        return isset($this->getRegistry()->data['table'][$moduleName]);
    }
    /**
     * Checks if json file has already been scanned
     *
     * @param string $jsonFilePath
     * @return bool
     */
    private function isJsonAlreadyScanned(string $jsonFilePath): bool
    {
        $moduleName = $this->getModuleNameByPath->resolveByEtcDirFilePath($jsonFilePath);
        return isset($this->getRegistry()->data['whitelist_json'][$moduleName]);
    }
    /**
     * Detects and scans db_schema_whitelist.json when scanning db_schema.xml
     *
     * @param string $xmlFilePath
     */
    private function associateJsonWithProvidedXmlPath(string $xmlFilePath): void
    {
        $basedir = dirname($xmlFilePath);
        $jsonFilePath = $basedir . "/" . self::DB_SCHEMA_WHITELIST_JSON;
        if (file_exists($jsonFilePath) && !$this->isJsonAlreadyScanned($jsonFilePath)) {
            $this->registry->setCurrentFile($jsonFilePath);
            $this->scanJson($jsonFilePath);
            $this->registry->setCurrentFile($xmlFilePath);
        }
    }
    /**
     * Detects and scans db_schema.xml when scanning db_schema_whitelist.json
     *
     * @param string $jsonFilePath
     */
    private function associateXmlWithProvidedJsonPath(string $jsonFilePath): void
    {
        $basedir = dirname($jsonFilePath);
        $xmlFilePath = $basedir . "/" . self::DB_SCHEMA_XML;
        if (file_exists($xmlFilePath) && !$this->isXmlAlreadyScanned($xmlFilePath)) {
            $this->registry->setCurrentFile($xmlFilePath);
            $this->scanXml($xmlFilePath);
            $this->registry->setCurrentFile($jsonFilePath);
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
        $moduleName = $this->getModuleNameByPath->resolveByEtcDirFilePath($this->registry->getCurrentFile());
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
        } elseif ($type === 'index' ) {
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
        $columnsArray= [];
        if (!isset($typeData['column'])){
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
