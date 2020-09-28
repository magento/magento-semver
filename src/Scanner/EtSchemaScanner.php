<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Scanner;

use Magento\SemanticVersionChecker\Registry\XmlRegistry;
use Magento\SemanticVersionChecker\Scanner\EtSchema\XmlConverter;
use PHPSemVerChecker\Registry\Registry;

/**
 * Class EtSchemaScanner
 */
class EtSchemaScanner
{
    /**
     * @var XmlRegistry
     */
    private $registry;

    /**
     * @var ModuleNamespaceResolver
     */
    private $getModuleNameByPath;

    /**
     * @var XmlConverter
     */
    private $converter;

    /**
     * EtSchemaScanner constructor.
     *
     * @param XmlRegistry $registry
     * @param ModuleNamespaceResolver $getModuleNameByPath
     * @param XmlConverter $converter
     */
    public function __construct(
        XmlRegistry $registry,
        ModuleNamespaceResolver $getModuleNameByPath,
        XmlConverter $converter
    ) {
        $this->registry = $registry;
        $this->getModuleNameByPath = $getModuleNameByPath;
        $this->converter = $converter;
    }

    /**
     * @param string $file
     */
    public function scan(string $file): void
    {
        $doc = new \DOMDocument();
        $doc->loadXML(file_get_contents($file));
        $moduleName = $this->getModuleNameByPath->resolveByEtcDirFilePath($file);
        $data = $this->converter->convert($doc);
        $this->getRegistry()->data['etSchema'][$moduleName] = $data;
    }

    /**
     * @return XmlRegistry
     */
    public function getRegistry(): Registry
    {
        return $this->registry;
    }
}
