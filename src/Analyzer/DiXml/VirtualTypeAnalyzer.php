<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionCheckr\Analyzer\DiXml;

use Magento\SemanticVersionCheckr\Analyzer\AnalyzerInterface;
use Magento\SemanticVersionCheckr\Node\VirtualType;
use Magento\SemanticVersionCheckr\Operation\DiXml\VirtualTypeChanged;
use Magento\SemanticVersionCheckr\Operation\DiXml\VirtualTypeRemoved;
use Magento\SemanticVersionCheckr\Registry\XmlRegistry;
use PHPSemVerChecker\Registry\Registry;
use PHPSemVerChecker\Report\Report;

/**
 * VirtualType
 * Performs comparison of <b>di.xml</b> and creates reports such as:
 * - virtual type removed
 */
class VirtualTypeAnalyzer implements AnalyzerInterface
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
     * Compared registryBefore and registryAfter find changes for di virtual types.
     *
     * @param XmlRegistry|Registry $registryBefore
     * @param XmlRegistry|Registry $registryAfter
     *
     * @return Report
     */
    public function analyze($registryBefore, $registryAfter)
    {
        $nodesBefore = $this->getVirtualTypeNode($registryBefore);
        $nodesAfter = $this->getVirtualTypeNode($registryAfter);

        if ($nodesBefore === $nodesAfter) {
            return $this->report;
        }

        foreach ($nodesBefore as $moduleName => $moduleNodes) {
            /* @var VirtualType $nodeBefore */
            foreach ($moduleNodes as $name => $nodeBefore) {
                // search nodesAfter the by name
                $nodeAfter = $nodesAfter[$moduleName][$name] ?? false;

                if ($nodeAfter !== false && $nodeBefore !== $nodeAfter) {
                    /* @var VirtualType $nodeAfter */
                    $this->triggerNodeChange($nodeBefore, $nodeAfter);
                    continue;
                }

                $operation = new VirtualTypeRemoved($moduleName, $name);
                $this->report->add('di', $operation);
            }
        }

        return $this->report;
    }

    /**
     * Return a filtered node list from type {@link VirtualType}
     *
     * @param XmlRegistry $xmlRegistry
     * @return VirtualType[]
     */
    private function getVirtualTypeNode(XmlRegistry $xmlRegistry): array
    {
        $virtualTypeNodeList = [];

        foreach ($xmlRegistry->getNodes() as $moduleName => $nodeList) {
            foreach ($nodeList as $node) {
                if ($node instanceof VirtualType === false) {
                    continue;
                }

                /** @var  VirtualType $node */
                $virtualTypeNodeList[$moduleName][$node->getName()] = $node;
            }
        }

        return $virtualTypeNodeList;
    }

    /**
     * Add node changed to report.
     *
     * @param VirtualType $nodeBefore
     * @param VirtualType $nodeAfter
     */
    private function triggerNodeChange(VirtualType $nodeBefore, VirtualType $nodeAfter): void
    {
        $bcFieldBefore = [
            'type' => $nodeBefore->getType(),
        ];
        $bcFieldAfter = [
            'type' => $nodeAfter->getType(),
        ];

        if ($bcFieldBefore === $bcFieldAfter && $nodeAfter->getScope() === 'global') {
            // scope was changed to global no breaking change
            return;
        }

        $bcFieldBefore['scope'] = $nodeBefore->getScope();
        $bcFieldAfter['scope'] = $nodeAfter->getScope();

        foreach ($bcFieldBefore as $fieldName => $valueBefore) {
            $valueAfter = $bcFieldAfter[$fieldName];
            if ($valueBefore !== $valueAfter) {
                $operation = new VirtualTypeChanged($nodeBefore->getName(), $fieldName);
                $this->report->add('di', $operation);
            }
        }
    }
}
