<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionCheckr\ClassHierarchy;

use PhpParser\Error;
use PhpParser\NodeTraverser;
use PhpParser\Parser;

/**
 * Implements an analyzer that builds a dependency graph of classes, interfaces and traits.
 */
class StaticAnalyzer
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var NodeTraverser
     */
    private $nodeTraverser;

    /**
     * @var DependencyInspectionVisitor
     */
    private $dependencyInspectionVisitor;

    /**
     * Constructor.
     *
     * @param Parser $phpParser
     * @param DependencyInspectionVisitor $dependencyInspectionVisitor
     * @param NodeTraverser $nodeTraverser
     */
    public function __construct(
        Parser $phpParser,
        DependencyInspectionVisitor $dependencyInspectionVisitor,
        NodeTraverser $nodeTraverser
    ) {
        $this->parser                      = $phpParser;
        $this->nodeTraverser               = $nodeTraverser;
        $this->dependencyInspectionVisitor = $dependencyInspectionVisitor;

        $this->nodeTraverser->addVisitor($dependencyInspectionVisitor);
    }

    /**
     * @param array $fileIterator
     * @return DependencyGraph
     * @throws \RuntimeException
     */
    public function analyse(array $fileIterator): DependencyGraph
    {
        foreach ($fileIterator as $file) {
            if ($this->isPhpFile($file) === false) {
                continue;
            }
            $code = file_get_contents($file);
            try {
                $this->nodeTraverser->traverse(
                    $this->parser->parse($code)
                );
            } catch (Error $e) {
                throw new \RuntimeException($e->getMessage() . ' ' . $file);
            }
        }

        return $this->dependencyInspectionVisitor->getDependencyGraph();
    }

    /**
     * @param string $filename
     * @return bool
     */
    private function isPhpFile(string $filename): bool
    {
        return (substr($filename, -4) === '.php');
    }
}
