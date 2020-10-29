<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Analyzer;

/**
 * API Class analyzer.
 * Performs comparison of API classes and creates reports such as:
 * - class added
 * - class removed
 * Runs method, constant, and property analyzers.
 */
class ApiClassAnalyzer extends ClassAnalyzer
{
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
        return array_merge(
            [new ClassLikeApiAnnotationAnalyzer($context, $fileBefore, $fileAfter, $this->dependencyGraph)],
            parent::getContentAnalyzers($context, $fileBefore, $fileAfter)
        );
    }
}
