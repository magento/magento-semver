<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Scanner;

use DOMDocument;
use DOMNode;
use DOMNodeList;
use Magento\SemanticVersionChecker\Node\Layout\Block;
use Magento\SemanticVersionChecker\Node\Layout\Container;
use Magento\SemanticVersionChecker\Node\Layout\Update;
use Magento\SemanticVersionChecker\Registry\XmlRegistry;
use PHPSemVerChecker\Registry\Registry;

/**
 * Handle *layout xml files* and tokenize them to the different types:
 * - `<block>`  {@link \Magento\SemanticVersionChecker\Node\Layout\Block}
 * - `<container>` {@link \Magento\SemanticVersionChecker\Node\Layout\Container}
 * - `<update>`  {@link \Magento\SemanticVersionChecker\Node\Layout\Update}
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
        $this->registry->setCurrentFile($file);
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
            $layoutNode = new Container($name, $label);
            $uniqueKey = $layoutNode->getUniqueKey();
            $this->registry->mapping[XmlRegistry::NODES_KEY][$moduleName][$uniqueKey] = $this->getRegistry()->getCurrentFile();
            $this->registry->addXmlNode($moduleName, $layoutNode);
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
            $layoutNode = new Block($name, $class, $template, $cacheable);
            $uniqueKey = $layoutNode->getUniqueKey();
            $this->registry->mapping[XmlRegistry::NODES_KEY][$moduleName][$uniqueKey] = $this->getRegistry()->getCurrentFile();

            $this->registry->addXmlNode($moduleName, $layoutNode);
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
            $layoutNode = new Update($handle);
            $uniqueKey = $layoutNode->getUniqueKey();
            $this->registry->mapping[XmlRegistry::NODES_KEY][$moduleName][$uniqueKey] = $this->getRegistry()->getCurrentFile();
            $this->registry->addXmlNode($moduleName, $layoutNode);
        }
    }
}
