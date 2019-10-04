<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tools\SemanticVersionChecker\Scanner;

use DOMDocument;
use DOMNode;
use DOMNodeList;
use Magento\Tools\SemanticVersionChecker\Node\Layout\Block;
use Magento\Tools\SemanticVersionChecker\Node\Layout\Container;
use Magento\Tools\SemanticVersionChecker\Node\Layout\Update;
use Magento\Tools\SemanticVersionChecker\Registry\XmlRegistry;
use PHPSemVerChecker\Registry\Registry;

/**
 * Handle *layout xml files* and tokenize them to the different types:
 * - `<block>`  {@link \Magento\Tools\SemanticVersionChecker\Node\Layout\Block}
 * - `<container>` {@link \Magento\Tools\SemanticVersionChecker\Node\Layout\Container}
 * - `<update>`  {@link \Magento\Tools\SemanticVersionChecker\Node\Layout\Update}
 */
class LayoutConfigScanner implements ScannerInterface
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
     * @param XmlRegistry $registry
     * @param ModuleNamespaceResolver $getModuleNameByPath
     */
    public function __construct(XmlRegistry $registry, ModuleNamespaceResolver $getModuleNameByPath)
    {
        $this->registry = $registry;
        $this->getModuleNameByPath = $getModuleNameByPath;
    }

    /**
     * @param string $file
     */
    public function scan(string $file): void
    {
        $doc = new DOMDocument();
        $doc->loadXML(file_get_contents($file));
        $moduleName = $this->getModuleNameByPath->resolveByViewDirFilePath($file);
        $this->registerContainerNodes($doc->getElementsByTagName('container'), $moduleName);
        $this->registerBlockNodes($doc->getElementsByTagName('block'), $moduleName);
        $this->registerUpdateNodes($doc->getElementsByTagName('update'), $moduleName);
    }

    /**
     * @return XmlRegistry
     */
    public function getRegistry(): Registry
    {
        return $this->registry;
    }

    /**
     * @param DOMNodeList $getElementsByTagName
     * @param string $moduleName
     */
    private function registerContainerNodes(DOMNodeList $getElementsByTagName, string $moduleName): void
    {
        /** @var DOMNode $node */
        foreach ($getElementsByTagName as $node) {
            $name = $node->getAttribute('name') ?? '';
            $label = $node->getAttribute('label') ?? '';
            $this->registry->addXmlNode($moduleName, new Container($name, $label));
        }
    }

    /**
     * @param DOMNodeList $getElementsByTagName
     * @param string $moduleName
     */
    private function registerBlockNodes(DOMNodeList $getElementsByTagName, string $moduleName): void
    {
        /** @var DOMNode $node */
        foreach ($getElementsByTagName as $node) {
            $name = $node->getAttribute('name') ?? '';
            $class = $node->getAttribute('class') ?? '';
            $template = $node->getAttribute('template') ?? '';
            $cacheable = true;
            if ($node->getAttribute('cacheable') === 'false') {
                $cacheable = false;
            }

            $this->registry->addXmlNode($moduleName, new Block($name, $class, $template, $cacheable));
        }
    }

    /**
     * @param DOMNodeList $getElementsByTagName
     * @param string $moduleName
     */
    private function registerUpdateNodes(DOMNodeList $getElementsByTagName, string $moduleName): void
    {
        /** @var DOMNode $node */
        foreach ($getElementsByTagName as $node) {
            $handle =  $node->getAttribute('handle') ?? '';
            $this->registry->addXmlNode($moduleName, new Update($handle));
        }
    }
}
