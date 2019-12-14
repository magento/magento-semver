<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Analyzer\Xsd;

use Magento\SemanticVersionChecker\Analyzer\AnalyzerInterface;
use Magento\SemanticVersionChecker\Node\Xsd\AttributeNode;
use Magento\SemanticVersionChecker\Node\Xsd\ElementNode;
use Magento\SemanticVersionChecker\Node\Xsd\NodeInterface;
use Magento\SemanticVersionChecker\Operation\Xsd\AttributeRemoved;
use Magento\SemanticVersionChecker\Operation\Xsd\NodeRemoved;
use Magento\SemanticVersionChecker\Operation\Xsd\OptionalAttributeAdded;
use Magento\SemanticVersionChecker\Operation\Xsd\OptionalNodeAdded;
use Magento\SemanticVersionChecker\Operation\Xsd\RequiredAttributeAdded;
use Magento\SemanticVersionChecker\Operation\Xsd\RequiredNodeAdded;
use Magento\SemanticVersionChecker\Operation\Xsd\SchemaDeclarationAdded;
use Magento\SemanticVersionChecker\Operation\Xsd\SchemaDeclarationRemoved;
use Magento\SemanticVersionChecker\Registry\XmlRegistry;
use PHPSemVerChecker\Registry\Registry;
use PHPSemVerChecker\Report\Report;

/**
 * Analyzer for XSD files.
 * Performs comparison of xsd files and creates reports such as:
 * - optional node or attribute added
 * - required node or attribute added
 * - node or attribute removed
 * - schema declaration added
 * - schema declaration removed
 */
class Analyzer implements AnalyzerInterface
{
    public const CONTEXT = 'xsd';

    /**
     * @var Report
     */
    private $report;

    /**
     * @param Report $report
     */
    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    /**
     * Compare with a destination registry (what the new source code is like)
     *
     * @param XmlRegistry|Registry $registryBefore
     * @param XmlRegistry|Registry $registryAfter
     *
     * @return Report
     */
    public function analyze($registryBefore, $registryAfter)
    {
        $nodesBefore = $this->getNodes($registryBefore);
        $nodesAfter  = $this->getNodes($registryAfter);

        if ($nodesBefore === $nodesAfter) {
            return $this->report;
        }

        $addedModules   = array_diff_key($nodesAfter, $nodesBefore);
        $removedModules = array_diff_key($nodesBefore, $nodesAfter);
        $commonModules  = array_intersect(array_keys($nodesBefore), array_keys($nodesAfter));

        //process added modules
        $this->reportAddedModules($addedModules);

        //process removed modules
        $this->reportRemovedModules($removedModules);

        //process common modules
        foreach ($commonModules as $moduleName) {
            $nodesBeforeByModule = array_key_exists($moduleName, $nodesBefore) ? $nodesBefore[$moduleName] : [];
            $nodesAfterByModule = array_key_exists($moduleName, $nodesAfter) ? $nodesAfter[$moduleName] : [];
            $filesBefore  = array_keys($nodesBeforeByModule);
            $filesAfter   = array_keys($nodesAfterByModule);

            //compute differences
            $addedFiles   = array_diff($filesAfter, $filesBefore);
            $removedFiles = array_diff($filesBefore, $filesAfter);
            $commonFiles  = array_intersect($filesBefore, $filesAfter);

            //process added files
            $this->reportAddedSchemaDeclarations($moduleName, $addedFiles);

            //process removed files
            $this->reportRemovedSchemaDeclarations($moduleName, $removedFiles);

            //process common files
            foreach ($commonFiles as $fileName) {
                $nodesAfter  = $nodesAfter[$moduleName][$fileName] ?: [];
                $nodesBefore = $nodesBefore[$moduleName][$fileName] ?: [];

                //compute differences
                $addedNodes = array_diff_key($nodesAfter, $nodesBefore);
                $removedNodes = array_diff_key($nodesBefore, $nodesAfter);

                //process added nodes
                $this->reportAddedNodes($moduleName, $addedNodes);

                //process removed nodes
                $this->reportRemovedNodes($moduleName, $removedNodes);
            }
        }

        return $this->report;
    }

    /**
     * Return node list by module name
     *
     * @param XmlRegistry $registry
     *
     * @return array
     */
    private function getNodes(XmlRegistry $registry): array
    {
        $nodes = [];

        foreach ($registry->getNodes() as $moduleName => $files) {
            if (!isset($nodes[$moduleName])) {
                $nodes[$moduleName] = [];
            }

            foreach ($files as $filePath => $content) {
                $fileNodes = [];

                /** @var NodeInterface $node */
                foreach ($content as $node) {
                    $nodeKey             = $node->getUniqueKey();
                    $fileNodes[$nodeKey] = $node;
                }

                $nodes[$moduleName][$filePath] = $fileNodes;
            }
        }

        //remove all empty and null nodes from array (see MC-22140)
        foreach ($nodes as $moduleName => $moduleNodes) {
            if ($moduleNodes === null || count($moduleNodes) === 0) {
                unset($nodes[$moduleName]);
            }
        }

        return $nodes;
    }

    /**
     * Creates reports for <var>$modules</var> that have been added.
     *
     * @param array $modules
     */
    private function reportAddedModules(array $modules): void
    {
        foreach ($modules as $moduleName => $files) {
            $fileNames = array_keys($files);
            $this->reportAddedSchemaDeclarations($moduleName, $fileNames);
        }
    }

    /**
     * Creates reports for <var>$nodes</var> that have been added in <var>$file</var> of <var>$module</var>.
     *
     * @param string $module
     * @param NodeInterface[] $nodes
     */
    private function reportAddedNodes(string $module, array $nodes): void
    {
        foreach ($nodes as $node) {
            switch (true) {
                case $node instanceof AttributeNode:
                    $data = $node->isRequired()
                        ? new RequiredAttributeAdded($module, $node->getName())
                        : new OptionalAttributeAdded($module, $node->getName());

                    $this->report->add(self::CONTEXT, $data);
                    break;
                case $node instanceof ElementNode:
                    $data = $node->isRequired()
                        ? new RequiredNodeAdded($module, $node->getName())
                        : new OptionalNodeAdded($module, $node->getName());

                    $this->report->add(self::CONTEXT, $data);
                    break;
                default:
                    //NOP: Unknown node type encountered, remains unhandled as we do not validate
            }
        }
    }

    /**
     * Creates reports for <var>$files</var> in <var>$module</var> that have been added.
     *
     * @param string $module
     * @param string[] $files
     */
    private function reportAddedSchemaDeclarations(string $module, array $files): void
    {
        foreach ($files as $file) {
            $this->report->add(self::CONTEXT, new SchemaDeclarationAdded($module, $file));
        }
    }

    /**
     * Creates reports for <var>$modules</var> that have been removed.
     *
     * @param array $modules
     */
    private function reportRemovedModules(array $modules): void
    {
        foreach ($modules as $moduleName => $files) {
            $fileNames = array_keys($files);
            $this->reportRemovedSchemaDeclarations($moduleName, $fileNames);
        }
    }

    /**
     * Creates reports for <var>$nodes</var> that have been removed from <var>$file</var> in <var>$module</var>.
     *
     * @param string $module
     * @param NodeInterface[] $nodes
     */
    private function reportRemovedNodes(string $module, array $nodes): void
    {
        foreach ($nodes as $node) {
            switch (true) {
                case $node instanceof AttributeNode:
                    $data = new AttributeRemoved($module, $node->getName());

                    $this->report->add(self::CONTEXT, $data);
                    break;
                case $node instanceof ElementNode:
                    $data = new NodeRemoved($module, $node->getName());

                    $this->report->add(self::CONTEXT, $data);
                    break;
                default:
                    //NOP: Unknown node type encountered, remains unhandled as we do not validate
            }
        }
    }

    /**
     * Creates reports for <var>$files</var> that have been removed in <var>$module</var>
     *
     * @param string $module
     * @param array $files
     */
    private function reportRemovedSchemaDeclarations(string $module, array $files): void
    {
        foreach ($files as $file) {
            $this->report->add(self::CONTEXT, new SchemaDeclarationRemoved($module, $file));
        }
    }
}
