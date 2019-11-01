<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionChecker\ClassHierarchy;

use Magento\SemanticVersionChecker\Helper\Node as NodeHelper;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;

/**
 * Implements a factory that creates new instances of {@link StaticAnalyzer}.
 */
class StaticAnalyzerFactory
{
    /**
     * @return StaticAnalyzer
     */
    public function create(): StaticAnalyzer
    {
        $parser                      = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $dependencyInspectionVisitor = new DependencyInspectionVisitor(
            new DependencyGraph(new EntityFactory()),
            new NodeHelper()
        );
        $nodeTraverser               = new NodeTraverser();

        $nodeTraverser->addVisitor(new NameResolver());

        return new StaticAnalyzer($parser, $dependencyInspectionVisitor, $nodeTraverser);
    }
}
