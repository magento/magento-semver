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
use Magento\SemanticVersionChecker\Node\Xsd\AttributeNode;
use Magento\SemanticVersionChecker\Node\Xsd\ElementNode;
use Magento\SemanticVersionChecker\Node\Xsd\NodeInterface;
use Magento\SemanticVersionChecker\Registry\XmlRegistry;
use PHPSemVerChecker\Registry\Registry;

class XsdScanner implements ScannerInterface
{
    /**
     * @var XmlRegistry
     */
    private $registry;

    /**
     * @var ModuleNamespaceResolver
     */
    private $moduleNameResolver;

    /**
     * Constructor.
     *
     * @param XmlRegistry $registry
     * @param ModuleNamespaceResolver $moduleNameResolver
     */
    public function __construct(
        XmlRegistry $registry,
        ModuleNamespaceResolver $moduleNameResolver
    ) {
        $this->registry           = $registry;
        $this->moduleNameResolver = $moduleNameResolver;
    }

    /**
     * @return XmlRegistry|Registry
     */
    public function getRegistry(): Registry
    {
        return $this->registry;
    }

    /**
     * @param string $file
     */
    public function scan(string $file): void
    {
        $doc = new DOMDocument();

        //prepare registry
        $this->registry->setCurrentFile($file);
        $this->registry->data[XmlRegistry::NODES_KEY][$file] = [];

        //load xsd file in DOMDocument process it
        $doc->load($file);
        $this->registerElementNodes($doc->getElementsByTagName('element'));
        $this->registerAttributeNodes($doc->getElementsByTagName('attribute'));
    }

    /**
     * Adds <var>$attributeNodes</var> to registry.
     *
     * @param DOMNodeList|DOMElement[] $attributeNodes
     */
    private function registerAttributeNodes(DOMNodeList $attributeNodes)
    {
        foreach ($attributeNodes as $attributeNode) {
            $isRequired = $attributeNode->hasAttribute('use') && $attributeNode->getAttribute('use') === 'required';
            $attribute  = new AttributeNode(
                $attributeNode->getAttribute('name'),
                $attributeNode->getAttribute('type'),
                $isRequired
            );

            $this->registerNode($attribute);
        }
    }

    /**
     * Adds <var>$elementNodes</var> to registry.
     *
     * @param DOMNodeList|DOMElement[] $elementNodes
     */
    private function registerElementNodes(DOMNodeList $elementNodes): void
    {
        foreach ($elementNodes as $elementNode) {
            $minOccurrence = $elementNode->getAttribute('minOccurs');
            $maxOccurrence = $elementNode->getAttribute('maxOccurs');

            //normalize minOccurrence
            if (strlen($minOccurrence) === 0) {
                $minOccurrence = 1;
            } else {
                $minOccurrence = (int)$minOccurrence;
            }

            //normalize mxOccurrence
            if ($maxOccurrence === 'unbounded') {
                $maxOccurrence = null;
            } elseif (strlen($maxOccurrence) === 0) {
                $maxOccurrence = 1;
            } else {
                $maxOccurrence = (int)$maxOccurrence;
            }

            $element = new ElementNode(
                $elementNode->getAttribute('name'),
                $elementNode->getAttribute('type'),
                $minOccurrence,
                $maxOccurrence
            );

            $this->registerNode($element);
        }
    }

    /**
     * Registers node in registry.
     *
     * @param NodeInterface $node
     */
    private function registerNode(NodeInterface $node)
    {
        $file             = $this->registry->getCurrentFile();
        $moduleName       = $this->moduleNameResolver->resolveByEtcDirFilePath($file);
        $relativeFilePath = $this->getRelativePath($file, $moduleName);

        $this->registry->data[XmlRegistry::NODES_KEY][$moduleName][$relativeFilePath][] = $node;
        $this->registry->mapping[XmlRegistry::NODES_KEY][$moduleName][$relativeFilePath] = $file;
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
}
