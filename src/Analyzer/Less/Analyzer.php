<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Analyzer\Less;

use Magento\SemanticVersionChecker\Analyzer\AnalyzerInterface;
use Magento\SemanticVersionChecker\Operation\Less\ImportRemoved;
use Magento\SemanticVersionChecker\Operation\Less\MixinParameterAdded;
use Magento\SemanticVersionChecker\Operation\Less\VariableRemoved;
use Magento\SemanticVersionChecker\Operation\Less\MixinRemoved;
use Magento\SemanticVersionChecker\Registry\LessRegistry;
use Magento\SemanticVersionChecker\Registry\XmlRegistry;
use PHPSemVerChecker\Registry\Registry;
use PHPSemVerChecker\Report\Report;
use Less_Tree;
use Less_Tree_Comment;
use Less_Tree_Rule;
use Less_Tree_Mixin_Definition;
use Less_Tree_Import;

/**
 * Analyzes <kbd>*.less</kbd> files:
 * - Removed <kbd>variable</kbd> nodes
 * - Removed <kbd>mixin</kbd> nodes
 * - Removed <kbd>import</kbd> nodes
 * - Added <kbd>mixin parameter</kbd> nodes
 */
class Analyzer implements AnalyzerInterface
{
    public const CONTEXT = 'less';

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
     * @param LessRegistry|Registry $registryBefore
     * @param LessRegistry|Registry $registryAfter
     * @return Report
     */
    public function analyze($registryBefore, $registryAfter)
    {
        $nodesBefore = $this->getNodes($registryBefore);
        $nodesAfter = $this->getNodes($registryAfter);

        //bail out if there are no differences
        if ($nodesBefore === $nodesAfter) {
            return $this->report;
        }

        $modulesBefore = array_keys($nodesBefore);
        $modulesAfter = array_keys($nodesAfter);
        $commonModules = array_intersect($modulesBefore, $modulesAfter);

        //process common files
        foreach ($commonModules as $moduleName) {
            $moduleLessFilesBefore = $nodesBefore[$moduleName];
            $moduleLessFilesAfter = $nodesAfter[$moduleName];

            $commonLessFiles = array_intersect_key($moduleLessFilesBefore, $moduleLessFilesAfter);

            foreach (array_keys($commonLessFiles) as $lessFileName) {
                $lessNodesBefore = $moduleLessFilesBefore[$lessFileName];
                $lessNodesAfter = $moduleLessFilesAfter[$lessFileName];

                $addedNodeNames = array_diff(array_keys($lessNodesAfter), array_keys($lessNodesBefore));
                $removedNodeNames = array_diff(array_keys($lessNodesBefore), array_keys($lessNodesAfter));

                if (count($removedNodeNames)) {
                    //report removals
                    $removedNodes = array_intersect_key($lessNodesBefore, array_flip($removedNodeNames));
                    $fileBefore = $registryBefore->mapping[LessRegistry::NODES_KEY][$moduleName][$lessFileName];
                    $this->reportRemovedNodes($fileBefore, $removedNodes);
                } elseif (!count($addedNodeNames) && !count($removedNodeNames)) {
                    //report changes inside nodes
                    $fileAfter = $registryAfter->mapping[LessRegistry::NODES_KEY][$moduleName][$lessFileName];
                    $this->reportUpdatedNodes($fileAfter, $lessNodesBefore, $lessNodesAfter);
                }
            }
        }

        return $this->report;
    }

    /**
     * Return node list by module name
     *
     * @param LessRegistry $registry
     * @return array<string, array<string, array<string, Less_Tree>>>
     */
    private function getNodes(LessRegistry $registry): array
    {
        $nodes = [];

        foreach ($registry->getNodes() as $moduleName => $files) {
            if (!isset($nodes[$moduleName])) {
                $nodes[$moduleName] = [];
            }

            foreach ($files as $filePath => $content) {
                $fileNodes = [];

                /** @var Less_Tree $node */
                foreach ($content as $node) {
                    //skip comments
                    if ($node instanceof Less_Tree_Comment) {
                        continue;
                    }
                    if (property_exists($node, 'name')) {
                        $nodeKey = $node->name;
                    } elseif (property_exists($node, 'type') && property_exists($node, 'path')) {
                        if ($node->path->value instanceof \Less_Tree_Quoted 
                            || property_exists($node->path->value, 'value')) 
                        {                            $nodeKey = $node->type . ' with value: \'' . $node->path->value->value . '\'';
                        } else {
                            $nodeKey = $node->type . ' with value: \'' . $node->path->value . '\'';
                        }
                    } else {
                        $nodeKey = get_class($node);
                    }
                    $fileNodes[$nodeKey] = $node;
                }

                $nodes[$moduleName][$filePath] = $fileNodes;
            }
        }

        return $nodes;
    }

    /**
     * Creates reports for <var>$nodes</var> considering that they have been removed.
     *
     * @param string $file
     * @param Less_Tree[] $nodes
     */
    private function reportRemovedNodes(string $file, array $nodes)
    {
        foreach ($nodes as $nodeName => $node) {
            switch (true) {
                case $node instanceof Less_Tree_Rule:
                    $this->report->add(self::CONTEXT, new VariableRemoved($file, $nodeName));
                    break;
                case $node instanceof Less_Tree_Mixin_Definition:
                    $this->report->add(self::CONTEXT, new MixinRemoved($file, $nodeName));
                    break;
                case $node instanceof Less_Tree_Import:
                    $this->report->add(self::CONTEXT, new ImportRemoved($file, $nodeName));
                    break;
                default:
                    //NOP Unknown node type
            }
        }
    }

    /**
     * Creates reports for <var>changes inside $nodes</var>.
     *
     * @param string $file
     * @param Less_Tree[] $nodesBefore
     * @param Less_Tree[] $nodesAfter
     */
    private function reportUpdatedNodes(string $file, array $nodesBefore, array $nodesAfter)
    {
        foreach ($nodesAfter as $nodeName => $nodeAfter) {
            $mixinParamsAdded = '';
            if ($nodeAfter instanceof Less_Tree_Mixin_Definition) {
                $mixinParamsAdded = $this->getAddedMixinParams($nodesBefore[$nodeName], $nodeAfter);
            }
            switch (true) {
                case (!empty($mixinParamsAdded)):
                    $this->report->add(
                        self::CONTEXT,
                        new MixinParameterAdded($file, $nodeName . ':' . $mixinParamsAdded)
                    );
                    break;
                default:
                    //untracked change
            }
        }
    }

    /**
     * Return additional parameters of mixin node after.
     *
     * @param Less_Tree_Mixin_Definition $nodeBefore
     * @param Less_Tree_Mixin_Definition $nodeAfter
     * @return string
     */
    private function getAddedMixinParams(
        Less_Tree_Mixin_Definition $nodeBefore,
        Less_Tree_Mixin_Definition $nodeAfter
    ): string {
        $paramsBefore = $nodeBefore->params ?? [];
        $paramsAfter = $nodeAfter->params ?? [];
        $paramsAdded = [];
        if ($paramsBefore == [] && $paramsAfter != []) {
            $paramsAdded = $this->getMixinParamNames($paramsAfter);
        } elseif (count($paramsAfter) >= count($paramsBefore)) {
            $paramNamesBefore = $this->getMixinParamNames($paramsBefore);
            $paramNamesAfter = $this->getMixinParamNames($paramsAfter);
            $paramsAdded = array_diff($paramNamesAfter, $paramNamesBefore);
        }

        return implode(', ', $paramsAdded);
    }

    /**
     * Combine name values of params to simple array.
     *
     * @param array $params
     * @return array
     */
    private function getMixinParamNames(array $params): array
    {
        $paramNames = [];
        foreach ($params as $param) {
            $paramNames[] = $param['name'];
        }

        return $paramNames;
    }
}
