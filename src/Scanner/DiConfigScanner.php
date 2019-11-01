<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionCheckr\Scanner;

use DOMDocument;
use DOMNode;
use DOMNodeList;
use Magento\SemanticVersionCheckr\Node\VirtualType;
use Magento\SemanticVersionCheckr\Registry\XmlRegistry;
use PHPSemVerChecker\Registry\Registry;

class DiConfigScanner implements ScannerInterface
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
        $this->registerVirtualTypeNodes($doc->getElementsByTagName('virtualType'), $file);
    }

    /**
     * @param DOMNodeList $virtualTypeNodes
     * @param string $fileName
     */
    private function registerVirtualTypeNodes(DOMNodeList $virtualTypeNodes, string $fileName): void
    {
        $moduleName = $this->getModuleNameByPath->resolveByEtcDirFilePath($fileName);
        $scope = $this->getScopeFromFile($fileName);
        /** @var DOMNode $node */
        foreach ($virtualTypeNodes as $node) {
            $name = $node->getAttribute('name');
            $type = $node->getAttribute('type');
            $shared = $node->getAttribute('shared') !== 'false';
            $this->registry->addXmlNode($moduleName, new VirtualType($name, $scope, $type, $shared));
        }
    }

    /**
     * @param string $file
     * @return string
     */
    private function getScopeFromFile($file): string
    {
        $basename = basename(pathinfo($file, PATHINFO_DIRNAME));
        return $basename === 'etc' ? 'global' : $basename;
    }

    /**
     * @return XmlRegistry
     */
    public function getRegistry(): Registry
    {
        return $this->registry;
    }
}
