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
class DbSchemaScannerDecorator implements ScannerInterface
{

    /**
     * @var Registry
     */
    private $registry;


    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param string $file
     * @return void
     */
    public function scan(string $file): void
    {
        // Set the current file used by the registry so that we can tell where the change was scanned.
        $this->registry->setCurrentFile($file);
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
     * @param array $data
     */
    private function processTable(array $data)
    {
        $name = $data['@attributes']['name'];
        $moduleName = $this->getModuleName();
        $this->getRegistry()->data['table'][$moduleName][$name]['resource'] =
            $data['@attributes']['resource'];

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

    public function getRegistry(): Registry
    {
        return $this->registry;
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
}
