<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionCheckr\Scanner;

use Less_Exception_Parser;
use Magento\SemanticVersionCheckr\Registry\LessRegistry;
use Magento\SemanticVersionCheckr\Parser\LessParser;
use PHPSemVerChecker\Registry\Registry;
use Less_Tree;

/**
 * Handle *less files* and tokenize them by using less parser {@link \Less_Parser}
 */
class LessScanner implements ScannerInterface
{
    /**
     * @var LessRegistry
     */
    private $registry;

    /**
     * @var LessParser
     */
    private $lessParser;

    /**
     * @var ModuleNamespaceResolver
     */
    private $moduleNamespaceResolver;

    public function __construct(
        LessRegistry $registry,
        LessParser $lessParser,
        ModuleNamespaceResolver $moduleNamespaceResolver
    ) {
        $this->registry = $registry;
        $this->lessParser = $lessParser;
        $this->moduleNamespaceResolver = $moduleNamespaceResolver;
    }

    /**
     * Scan given file.
     *
     * @param string $file
     * @throws Less_Exception_Parser
     */
    public function scan(string $file): void
    {
        $this->registry->setCurrentFile($file);
        $this->lessParser->parseFile($file);

        $rules = $this->lessParser->getRules();

        foreach ($rules as $node) {
            $this->registerNode($node);
        }
    }

    /**
     * Registers node in registry.
     *
     * @param Less_Tree $node
     */
    private function registerNode(Less_Tree $node)
    {
        $file             = $this->registry->getCurrentFile();
        $moduleName       = $this->moduleNamespaceResolver->resolveByViewDirFilePath($file);
        $relativeFilePath = $this->getRelativePath($file, $moduleName);

        $this->registry->data[LessRegistry::NODES_KEY][$moduleName][$relativeFilePath][] = $node;
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

    /**
     * Return specific registry for less files.
     *
     * @return LessRegistry|Registry
     */
    public function getRegistry(): Registry
    {
        return $this->registry;
    }
}
