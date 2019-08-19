<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Scanner;

use PHPSemVerChecker\Registry\Registry;

/**
 * Class ScannerDecorator
 * @package Magento\SemanticVersionChecker\Scanner
 */
class DbSchemaScannerDecorator
{
    /**
     * @var Scanner
     */
    private $basicScanner;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param string $reportType "api|all"
     * @throws \Exception
     */
    public function __construct($reportType = 'all')
    {
        $this->basicScanner = new Scanner($reportType);
        $this->registry = $this->basicScanner->getRegistry();
    }

    /**
     * @param string $file
     * @return void
     */
    public function scan($file)
    {
        if ($this->isXml($file)) {
            // Set the current file used by the registry so that we can tell where the change was scanned.
            $this->registry->setCurrentFile($file);
            $xml = simplexml_load_file($file);
            $xmlResult = json_decode(json_encode($xml), true);

            if (isset($xmlResult['table']['@attributes'])) {
                $this->processTable($xmlResult['table']);
            } else {
                foreach ($xmlResult['table'] as $tableData) {
                    $this->processTable($tableData);
                }
            }
        } else {
            $this->basicScanner->scan($file);
        }
    }

    /**
     * Check whether content is XML file
     *
     * @param string $file
     * @return int
     */
    private function isXml($file)
    {
        return (bool) preg_match('/^.*.xml$/', $file);
    }

    /**
     * Prepare module name from path
     *
     * @return string
     */
    private function getModuleName()
    {
        $currentFile = $this->getRegistry()->getCurrentFile();
        $matches = [];
        preg_match('/^.*(Magento\/\w+).*.xml$/', $currentFile, $matches);
        return str_replace('/', '_', $matches[1]);
    }

    /**
     * @param string $name
     * @param string $type
     * @param array $typeData
     */
    private function registerObject($name, $type, array $typeData)
    {
        $typeName = $typeData['name'] ?? $typeData['referenceId'];
        $isForeignKey = isset($typeData['referenceTable']) && isset($typeData['referenceColumn']);
        if ($isForeignKey) {
            $type = 'foreign';
        } elseif ($type === 'index' || $type === 'constraint') {
            $type = 'key';
        }

        $this->getRegistry()->data['table'][$this->getModuleName()][$name][$type][$typeName] = $typeName;
    }

    /**
     * @param array $data
     */
    public function processTable(array $data)
    {
        $name = $data['@attributes']['name'];
        $moduleName = $this->getModuleName();
        $resource = isset($data['@attributes']['resource']) ? $data['@attributes']['resource'] : 'default';
        $this->getRegistry()->data['table'][$moduleName][$name]['resource'] = $resource;

        $types = ['column', 'constraint', 'index'];
        foreach ($types as $type) {
            if (isset($data[$type])) {
                if (isset($data[$type]['@attributes'])) {
                    $this->registerObject($name, $type, $data[$type]['@attributes']);
                } else {
                    foreach ($data[$type] as $typeData) {
                        if (isset($typeData['@attributes'])) {
                            $this->registerObject($name, $type, $typeData['@attributes']);
                        }
                    }
                }
            }
        }
    }

    /**
     * @return \PHPSemVerChecker\Registry\Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }
}
