<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Analyzer\Layout;

use Magento\SemanticVersionChecker\Analyzer\AnalyzerInterface;
use Magento\SemanticVersionChecker\Node\Layout\Block;
use Magento\SemanticVersionChecker\Node\Layout\Container;
use Magento\SemanticVersionChecker\Node\Layout\LayoutNodeInterface;
use Magento\SemanticVersionChecker\Node\Layout\Update;
use Magento\SemanticVersionChecker\Operation\Layout\BlockRemoved;
use Magento\SemanticVersionChecker\Operation\Layout\ContainerRemoved;
use Magento\SemanticVersionChecker\Operation\Layout\UpdateRemoved;
use Magento\SemanticVersionChecker\Registry\XmlRegistry;
use PHPSemVerChecker\Report\Report;

/**
 * Layout xml
 * Performs comparison of <b>layout xml </b> and creates reports such as:
 * - `<block>` is removed
 * - `<container>` is removed
 * - `<update>` is removed
 */
class Analyzer implements AnalyzerInterface
{
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
     * Compared registryBefore and registryAfter find changes for layout block types.
     *
     * @param XmlRegistry $registryBefore
     * @param XmlRegistry $registryAfter
     * @return Report
     */
    public function analyze($registryBefore, $registryAfter)
    {
        $nodesBefore = $registryBefore->getNodes();
        $nodesAfter = $registryAfter->getNodes();
        if ($nodesBefore === $nodesAfter) {
            return $this->report;
        }

        foreach (array_keys($nodesBefore) as $moduleName) {
            $moduleNodesBefore = $nodesBefore[$moduleName] ?? [];
            $moduleNodesAfter = [];

            /**
             * @var LayoutNodeInterface $node
             */
            foreach ($nodesAfter[$moduleName] ?? [] as $node) {
                $moduleNodesAfter[$moduleName][$node->getUniqueKey()] = $node;
            }

            /**
             * @var string $nodeName
             * @var LayoutNodeInterface $node
             */
            foreach ($moduleNodesBefore as $nodeName => $node) {
                $nodeAfter = $moduleNodesAfter[$moduleName][$node->getUniqueKey()] ?? false;
                if ($nodeAfter === false) {
                    $this->triggerNodeRemoved($moduleName, $node);
                }
            }
        }

        return $this->report;
    }

    /**
     * @param string $moduleName
     * @param $node
     */
    private function triggerNodeRemoved(string $moduleName, $node): void
    {
        if ($node instanceof Block) {
            $this->report->add('layout', new BlockRemoved($moduleName, $node->getName()));
            return;
        }

        if ($node instanceof Container) {
            $this->report->add('layout', new ContainerRemoved($moduleName, $node->getName()));
            return;
        }

        if ($node instanceof Update) {
            $this->report->add('layout', new UpdateRemoved($moduleName, $node->getHandle()));
            return;
        }
    }
}
