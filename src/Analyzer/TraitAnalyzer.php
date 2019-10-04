<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tools\SemanticVersionChecker\Analyzer;

use PhpParser\Node\Stmt\Trait_;
use PHPSemVerChecker\Registry\Registry;
use PHPSemVerChecker\Report\Report;

/**
 * Trait analyzer.
 * Runs method analyzer.
 */
class TraitAnalyzer extends AbstractCodeAnalyzer
{
    const CONTEXT = 'trait';

    /**
     * Get the name of a Trait_ node
     *
     * @param Trait_ $node
     * @return string
     */
    protected function getNodeName($node)
    {
        return $node->name;
    }

    /**
     * Use nodes of the Trait_ type for this analyzer
     *
     * @return string
     */
    protected function getNodeClass()
    {
        return Trait_::class;
    }

    /**
     * Get the trait node registry
     *
     * @param Registry $registry
     * @return Trait_[]
     */
    protected function getNodeNameMap($registry)
    {
        return $registry->data[static::CONTEXT];
    }

    /**
     * Get the filename from the registry
     *
     * @param Registry $registry
     * @param string $traitName
     * @param bool $isBefore
     * @return string|null
     */
    protected function getFileName($registry, $traitName, $isBefore = true)
    {
        return $registry->mapping[static::CONTEXT][$traitName] ?? null;
    }

    /**
     * Do nothing since we do not analyze added traits
     *
     * @param Report $report
     * @param string $fileAfter
     * @param Registry $registryAfter
     * @param Trait_ $traitAfter
     * @return void
     */
    protected function reportAddedNode($report, $fileAfter, $registryAfter, $traitAfter)
    {
        //NOP: We do not analyze added traits yet
    }

    /**
     * Do nothing since we do not analyze removed traits
     *
     * @param Report $report
     * @param string $fileBefore
     * @param Registry $registryBefore
     * @param Trait_ $traitBefore
     * @return void
     */
    protected function reportRemovedNode($report, $fileBefore, $registryBefore, $traitBefore)
    {
        //NOP: We do not analyze removed traits yet
    }

    /**
     * Find an report changes to existing traits
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
        $afterNameMap  = $this->getNodeNameMap($registryAfter);

        foreach ($toVerify as $traitName) {
            $traitBefore = $beforeNameMap[$traitName];
            $traitAfter  = $afterNameMap[$traitName];

            if ($traitBefore !== $traitAfter) {
                $fileBefore  = $this->getFileName($registryBefore, $traitName);
                $fileAfter   = $this->getFileName($registryAfter, $traitName);

                /** @var AbstractCodeAnalyzer[] $analyzers */
                $analyzers = [
                    new ClassMethodAnalyzer(static::CONTEXT, $fileBefore, $fileAfter),
                ];

                foreach ($analyzers as $analyzer) {
                    $internalReport = $analyzer->analyze($traitBefore, $traitAfter);
                    $report->merge($internalReport);
                }
            }
        }
    }
}
