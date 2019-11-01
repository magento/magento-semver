<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionCheckr\Analyzer\SystemXml;

use Magento\SemanticVersionCheckr\Analyzer\AnalyzerInterface;
use Magento\SemanticVersionCheckr\Node\SystemXml\Field;
use Magento\SemanticVersionCheckr\Node\SystemXml\Group;
use Magento\SemanticVersionCheckr\Node\SystemXml\NodeInterface;
use Magento\SemanticVersionCheckr\Node\SystemXml\Section;
use Magento\SemanticVersionCheckr\Operation\SystemXml\FieldAdded;
use Magento\SemanticVersionCheckr\Operation\SystemXml\FieldRemoved;
use Magento\SemanticVersionCheckr\Operation\SystemXml\FileAdded;
use Magento\SemanticVersionCheckr\Operation\SystemXml\FileRemoved;
use Magento\SemanticVersionCheckr\Operation\SystemXml\GroupAdded;
use Magento\SemanticVersionCheckr\Operation\SystemXml\GroupRemoved;
use Magento\SemanticVersionCheckr\Operation\SystemXml\SectionAdded;
use Magento\SemanticVersionCheckr\Operation\SystemXml\SectionRemoved;
use Magento\SemanticVersionCheckr\Registry\XmlRegistry;
use PHPSemVerChecker\Registry\Registry;
use PHPSemVerChecker\Report\Report;

/**
 * Analyzes <kbd>system.xml</kbd> files:
 * - Added and removed files
 * - Added and removed <kbd>section</kbd> nodes
 * - Added and removed <kbd>group</kbd> nodes
 * - Added and removed <kbd>field</kbd> nodes
 */
class Analyzer implements AnalyzerInterface
{
    /**
     * @var Report
     */
    private $report;

    /**
     * Constructor.
     *
     * @param Report $report
     */
    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    /**
     * Compare with a destination registry (what the new source code is like).
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

        //bail out if there are no differences
        if ($nodesBefore === $nodesAfter) {
            return $this->report;
        }

        $modulesBefore = array_keys($nodesBefore);
        $modulesAfter  = array_keys($nodesAfter);

        //process added / removed files
        $addedModules   = array_diff($modulesAfter, $modulesBefore);
        $commonModules  = array_intersect($modulesBefore, $modulesAfter);
        $removedModules = array_diff($modulesBefore, $modulesAfter);

        //process added files
        $this->reportAddedFiles($addedModules);

        //process removed files
        $this->reportRemovedFiles($removedModules);

        //process common files
        foreach ($commonModules as $moduleName) {
            $moduleNodesBefore = $nodesBefore[$moduleName];
            $moduleNodesAfter  = $nodesAfter[$moduleName];
            $addedNodes        = array_diff_key($moduleNodesAfter, $moduleNodesBefore);
            $removedNodes      = array_diff_key($moduleNodesBefore, $moduleNodesAfter);

            $this->reportAddedNodes($moduleName, $addedNodes);
            $this->reportRemovedNodes($moduleName, $removedNodes);
        }

        return $this->report;
    }

    /**
     * Extracts the node from <var>$registry</var> as an associative array.
     *
     * @param XmlRegistry $registry
     * @return array<string, array<string, NodeInterface>>
     */
    private function getNodes(XmlRegistry $registry): array
    {
        $nodes = [];

        foreach ($registry->getNodes() as $moduleName => $moduleNodes) {
            if (!isset($nodes[$moduleName])) {
                $nodes[$moduleName] = [];
            }

            /** @var NodeInterface $moduleNode */
            foreach ($moduleNodes as $moduleNode) {
                $nodeKey                      = $moduleNode->getUniqueKey();
                $nodes[$moduleName][$nodeKey] = $moduleNode;
            }
        }

        return $nodes;
    }

    /**
     * Creates reports for <var>$modules</var> considering that <kbd>system.xml</kbd> has been added to them.
     *
     * @param string[] $modules
     */
    private function reportAddedFiles(array $modules)
    {
        foreach ($modules as $module) {
            $this->report->add('system', new FileAdded($module, 'system.xml'));
        }
    }

    /**
     * Creates reports for <var>$nodes</var> considering that they have been added.
     *
     * @param string $file
     * @param NodeInterface[] $nodes
     */
    private function reportAddedNodes(string $file, array $nodes)
    {
        foreach ($nodes as $node) {
            switch (true) {
                case $node instanceof Section:
                    $this->report->add('system', new SectionAdded($file, $node->getPath()));
                    break;
                case $node instanceof Group:
                    $this->report->add('system', new GroupAdded($file, $node->getPath()));
                    break;
                case $node instanceof Field:
                    $this->report->add('system', new FieldAdded($file, $node->getPath()));
                    break;
                default:
                    //NOP - Unknown node types are simply ignored as we do not validate
            }
        }
    }

    /**
     * Creates reports for <var>$modules</var> considering that <kbd>system.xml</kbd> has been removed from them.
     *
     * @param array $modules
     */
    private function reportRemovedFiles(array $modules)
    {
        foreach ($modules as $module) {
            $this->report->add('system', new FileRemoved($module, 'system.xml'));
        }
    }

    /**
     * Creates reports for <var>$nodes</var> considering that they have been removed.
     *
     * @param string $file
     * @param NodeInterface[] $nodes
     */
    private function reportRemovedNodes(string $file, array $nodes)
    {
        foreach ($nodes as $node) {
            switch (true) {
                case $node instanceof Section:
                    $this->report->add('system', new SectionRemoved($file, $node->getPath()));
                    break;
                case $node instanceof Group:
                    $this->report->add('system', new GroupRemoved($file, $node->getPath()));
                    break;
                case $node instanceof Field:
                    $this->report->add('system', new FieldRemoved($file, $node->getPath()));
                    break;
                default:
                    //NOP Unknown node type
            }
        }
    }
}
