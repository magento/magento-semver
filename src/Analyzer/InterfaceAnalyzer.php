<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Analyzer;

use PhpParser\Node\Stmt\Interface_;
use PHPSemVerChecker\Operation\InterfaceAdded;
use PHPSemVerChecker\Operation\InterfaceRemoved;
use PHPSemVerChecker\Registry\Registry;
use PHPSemVerChecker\Report\Report;

/**
 * Interface analyzer.
 * Performs comparison of interfaces and creates reports such as:
 * - interface added
 * - interface removed
 * Runs method analyzer and constant analyzer.
 */
class InterfaceAnalyzer extends AbstractCodeAnalyzer
{
    public const CONTEXT = 'interface';

    /**
     * Get the name of an Interface_ node
     *
     * @param Interface_ $node
     * @return string
     */
    protected function getNodeName($node)
    {
        return $node->name;
    }

    /**
     * Use nodes of the Interface_ type for this analyzer
     *
     * @return string
     */
    protected function getNodeClass()
    {
        return Interface_::class;
    }

    /**
     * Get the interface node registry
     *
     * @param Registry $registry
     * @return Interface_[]
     */
    protected function getNodeNameMap($registry)
    {
        return $registry->data[static::CONTEXT];
    }

    /**
     * Get the filename from the registry
     *
     * @param Registry $registry
     * @param string $interfaceName
     * @param null $isBefore
     * @return string|null
     */
    protected function getFileName($registry, $interfaceName, $isBefore = null)
    {
        return $registry->mapping[static::CONTEXT][$interfaceName] ?? null;
    }

    /**
     * Create and report an InterfaceAdded operation
     *
     * @param Report $report
     * @param string $fileAfter
     * @param Registry $registryAfter
     * @param Interface_ $interfaceAfter
     * @return void
     */
    protected function reportAddedNode($report, $fileAfter, $registryAfter, $interfaceAfter)
    {
        $report->addInterface(new InterfaceAdded($fileAfter, $interfaceAfter));
    }

    /**
     * Create and report an InterfaceRemoved operation
     *
     * @param Report $report
     * @param string $fileBefore
     * @param Registry $registryBefore
     * @param Interface_ $nodeBefore
     */
    protected function reportRemovedNode($report, $fileBefore, $registryBefore, $nodeBefore)
    {
        $report->addInterface(new InterfaceRemoved($fileBefore, $nodeBefore));
    }

    /**
     * Find and report changes to existing interfaces using the constant and method analyzers
     *
     * @param Report $report
     * @param Registry $registryBefore
     * @param Registry $registryAfter
     * @param string[] $toVerify
     * @return void
     */
    protected function reportChanged($report, $registryBefore, $registryAfter, $toVerify)
    {
        $beforeNameMap = $this->getNodeNameMap($registryBefore);
        $afterNameMap = $this->getNodeNameMap($registryAfter);
        foreach ($toVerify as $key) {
            $fileBefore = $registryBefore->mapping[static::CONTEXT][$key];
            $interfaceBefore = $beforeNameMap[$key];
            $fileAfter = $registryAfter->mapping[static::CONTEXT][$key];
            $interfaceAfter = $afterNameMap[$key];

            if ($interfaceBefore !== $interfaceAfter) {
                /** @var AnalyzerInterface[] $analyzers */
                $analyzers = [
                    new ClassMethodAnalyzer(static::CONTEXT, $fileBefore, $fileAfter),
                    new ClassConstantAnalyzer(static::CONTEXT, $fileBefore, $fileAfter),
                    new ClassMethodExceptionAnalyzer(static::CONTEXT, $fileBefore, $fileAfter),
                    new InterfaceExtendsAnalyzer(static::CONTEXT, $fileBefore, $fileAfter)
                ];

                foreach ($analyzers as $analyzer) {
                    $internalReport = $analyzer->analyze($interfaceBefore, $interfaceAfter);
                    $report->merge($internalReport);
                }
            }
        }
    }
}
