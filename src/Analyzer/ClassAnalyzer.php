<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Analyzer;

use PhpParser\Node\Stmt\Class_;
use PHPSemVerChecker\Operation\ClassAdded;
use PHPSemVerChecker\Operation\ClassRemoved;
use PHPSemVerChecker\Registry\Registry;
use PHPSemVerChecker\Report\Report;

/**
 * Class analyzer.
 * Performs comparison of classes and creates reports such as:
 * - class added
 * - class removed
 * Runs method, constant, and property analyzers.
 */
class ClassAnalyzer extends AbstractCodeAnalyzer
{
    public const CONTEXT = 'class';

    /**
     * Get the name of a Class_ node
     *
     * @param Class_ $node
     * @return string
     */
    protected function getNodeName($node)
    {
        return $node->name;
    }

    /**
     * Use nodes of the Class_ type for this analyzer
     *
     * @return string
     */
    protected function getNodeClass()
    {
        return Class_::class;
    }

    /**
     * Get the filename from the registry
     *
     * @param  Registry $registry
     * @param  string   $className
     * @param  null     $isBefore
     * @return string|null
     */
    protected function getFileName($registry, $className, $isBefore = null)
    {
        return $registry->mapping[static::CONTEXT][$className] ?? null;
    }

    /**
     * Create and report a ClassAdded operation
     *
     * @param  Report   $report
     * @param  string   $fileAfter
     * @param  Registry $registryAfter
     * @param  Class_   $classAfter
     * @return void
     */
    protected function reportAddedNode($report, $fileAfter, $registryAfter, $classAfter)
    {
        $report->addClass(new ClassAdded($fileAfter, $classAfter));
    }

    /**
     * Create and report a ClassRemoved operation
     *
     * @param  Report   $report
     * @param  string   $fileBefore
     * @param  Registry $registryBefore
     * @param  Class_   $classBefore
     * @return void
     */
    protected function reportRemovedNode($report, $fileBefore, $registryBefore, $classBefore)
    {
        $report->addClass(new ClassRemoved($fileBefore, $classBefore));
    }

    /**
     * Find and report changes to existing classes using the constant, method, and property analyzers
     *
     * @param  Report   $report
     * @param  Registry $registryBefore
     * @param  Registry $registryAfter
     * @param  string[] $toVerify
     * @return void
     */
    protected function reportChanged($report, $registryBefore, $registryAfter, $toVerify)
    {
        $beforeNameMap = $this->getNodeNameMap($registryBefore);
        $afterNameMap = $this->getNodeNameMap($registryAfter);
        foreach ($toVerify as $key) {
            $fileBefore = $registryBefore->mapping[static::CONTEXT][$key];
            $classBefore = $beforeNameMap[$key];
            $fileAfter = $registryAfter->mapping[static::CONTEXT][$key];
            $classAfter = $afterNameMap[$key];

            if ($classBefore !== $classAfter) {
                $analyzers = $this->getContentAnalyzers(static::CONTEXT, $fileBefore, $fileAfter);

                foreach ($analyzers as $analyzer) {
                    $internalReport = $analyzer->analyze($classBefore, $classAfter);
                    $report->merge($internalReport);
                }
            }
        }
    }

    /**
     * Get the list of content analyzers
     *
     * @param string $context
     * @param string $fileBefore
     * @param string $fileAfter
     * @return AbstractCodeAnalyzer[]
     */
    protected function getContentAnalyzers($context, $fileBefore, $fileAfter)
    {
        return [
            new ClassMethodAnalyzer($context, $fileBefore, $fileAfter, $this->dependencyGraph),
            new PropertyAnalyzer($context, $fileBefore, $fileAfter),
            new ClassConstantAnalyzer($context, $fileBefore, $fileAfter),
            new ClassMethodExceptionAnalyzer($context, $fileBefore, $fileAfter),
            new ClassImplementsAnalyzer($context, $fileBefore, $fileAfter),
            new ClassExtendsAnalyzer($context, $fileBefore, $fileAfter),
            new ClassTraitAnalyzer($context, $fileBefore, $fileAfter),
        ];
    }

    /**
     * Get the class node registry
     *
     * @param  Registry $registry
     * @return Class_[]
     */
    protected function getNodeNameMap($registry)
    {
        return $registry->data[static::CONTEXT];
    }
}
