<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Scanner;

use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMXPath;
use Magento\SemanticVersionChecker\Node\SystemXml\Field;
use Magento\SemanticVersionChecker\Node\SystemXml\Group;
use Magento\SemanticVersionChecker\Node\SystemXml\NodeInterface;
use Magento\SemanticVersionChecker\Node\SystemXml\Section;
use Magento\SemanticVersionChecker\Registry\XmlRegistry;
use PHPSemVerChecker\Registry\Registry;

/**
 * Implements a scanner for <kbd>system.xml</kbd> files.
 */
class SystemXmlScanner implements ScannerInterface
{
    /**
     * @var ModuleNamespaceResolver
     */
    private $moduleNameResolver;

    /**
     * @var XmlRegistry
     */
    private $registry;

    /**
     * @var DOMXPath
     */
    private $xPath;

    /**
     * Constructor.
     *
     * @param XmlRegistry $registry
     * @param ModuleNamespaceResolver $moduleNameResolver
     */
    public function __construct(XmlRegistry $registry, ModuleNamespaceResolver $moduleNameResolver)
    {
        $this->registry           = $registry;
        $this->moduleNameResolver = $moduleNameResolver;
    }

    /**
     * @param string $file
     */
    public function scan(string $file): void
    {
        $doc        = new DOMDocument();
        $moduleName = $this->moduleNameResolver->resolveByEtcDirFilePath($file);
        $this->getRegistry()->mapping[XmlRegistry::NODES_KEY][$moduleName] = $file;

        $doc->load($file);
        $this->xPath = new DOMXPath($doc);

        $this->registerSectionNodes($moduleName, $doc->getElementsByTagName('section'));
    }

    /**
     * @return Registry
     */
    public function getRegistry(): Registry
    {
        return $this->registry;
    }

    /**
     * Registers <kbd>field</kbd> nodes.
     *
     * @param string $moduleName
     * @param NodeInterface $parent
     * @param DOMNodeList|DOMElement[] $fieldNodes
     */
    private function registerFieldNodes(string $moduleName, NodeInterface $parent, DOMNodeList $fieldNodes)
    {
        foreach ($fieldNodes as $fieldNode) {
            $id = $fieldNode->getAttribute('id');
            $field = new Field($id, $parent);

            $this->registry->addXmlNode($moduleName, $field);
        }
    }

    /**
     * Registers <kbd>group</kbd> nodes and triggers registration of <kbd>group</kbd> and <kbd>field</kbd> nodes they
     * contain.
     *
     * @param string $moduleName
     * @param NodeInterface $parent
     * @param DOMNodeList|DOMElement[] $groupNodes
     */
    private function registerGroupNodes(string $moduleName, NodeInterface $parent, DOMNodeList $groupNodes)
    {
        foreach ($groupNodes as $groupNode) {
            $id    = $groupNode->getAttribute('id');
            $group = new Group($id, $parent);

            $this->registry->addXmlNode($moduleName, $group);
            $this->registerGroupNodes($moduleName, $group, $this->xPath->query('group', $groupNode));
            $this->registerFieldNodes($moduleName, $group, $this->xPath->query('field', $groupNode));
        }
    }

    /**
     * Registers <kbd>section</kbd> nodes and triggers registration of <kbd>group</kbd> nodes they contain.
     *
     * @param string $moduleName
     * @param DOMNodeList|DOMElement[] $sectionNodes
     */
    private function registerSectionNodes(string $moduleName, DOMNodeList $sectionNodes)
    {
        foreach ($sectionNodes as $sectionNode) {
            $id      = $sectionNode->getAttribute('id');
            $section = new Section($id);

            $this->registry->addXmlNode($moduleName, $section);
            $this->registerGroupNodes($moduleName, $section, $this->xPath->query('group', $sectionNode));
        }
    }
}
