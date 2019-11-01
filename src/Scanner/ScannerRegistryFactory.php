<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionCheckr\Scanner;

use Magento\SemanticVersionCheckr\ClassHierarchy\DependencyGraph;
use Magento\SemanticVersionCheckr\Helper\Node as NodeHelper;
use Magento\SemanticVersionCheckr\Parser\LessParser;
use Magento\SemanticVersionCheckr\Registry\LessRegistry;
use Magento\SemanticVersionCheckr\Registry\XmlRegistry;
use Magento\SemanticVersionCheckr\ReportTypes;
use Magento\SemanticVersionCheckr\Visitor\ApiClassVisitor;
use Magento\SemanticVersionCheckr\Visitor\ApiInterfaceVisitor;
use Magento\SemanticVersionCheckr\Visitor\ApiTraitVisitor;
use PhpParser\Lexer\Emulative;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser\Php7 as Parser;
use PHPSemVerChecker\Registry\Registry;
use PHPSemVerChecker\Visitor\ClassVisitor;
use PHPSemVerChecker\Visitor\FunctionVisitor;
use PHPSemVerChecker\Visitor\InterfaceVisitor;
use PHPSemVerChecker\Visitor\TraitVisitor;

class ScannerRegistryFactory
{
    /**
     * @return Scanner
     */
    private function buildFullScanner()
    {
        $registry    = new Registry();
        $parser      = new Parser(new Emulative());
        $traverser   = new NodeTraverser();
        $apiVisitors = [
            new NameResolver(),
            new ClassVisitor($registry),
            new InterfaceVisitor($registry),
            new FunctionVisitor($registry),
            new TraitVisitor($registry),
        ];

        return new Scanner($registry, $parser, $traverser, $apiVisitors);
    }

    /**
     * @param DependencyGraph|null $dependencyGraph
     * @return Scanner
     */
    private function buildApiScanner(DependencyGraph $dependencyGraph = null)
    {
        $registry    = new Registry();
        $parser      = new Parser(new Emulative());
        $traverser   = new NodeTraverser();
        $nodeHelper  = new NodeHelper();
        $apiVisitors = [
            new NameResolver(),
            new ApiClassVisitor($registry, $nodeHelper, $dependencyGraph),
            new ApiInterfaceVisitor($registry, $nodeHelper, $dependencyGraph),
            new ApiTraitVisitor($registry, $nodeHelper, $dependencyGraph),
            new FunctionVisitor($registry),
        ];

        return new Scanner($registry, $parser, $traverser, $apiVisitors);
    }

    /**
     * @param DependencyGraph|null $dependencyGraph
     * @return array
     */
    public function create(DependencyGraph $dependencyGraph = null)
    {
        $moduleNameResolver = new ModuleNamespaceResolver();

        return [
            ReportTypes::ALL => [
                'pattern' => [
                    '*.php',
                ],
                'scanner' => $this->buildFullScanner(),
            ],
            ReportTypes::API => [
                'pattern' => [
                    '*.php',
                ],
                'scanner' => $this->buildApiScanner($dependencyGraph),
            ],
            ReportTypes::DB_SCHEMA => [
                'pattern' => [
                    'db_schema.xml',
                    'db_schema_whitelist.json',
                ],
                'scanner' => new DbSchemaScanner(new XmlRegistry(), $moduleNameResolver),
            ],
            ReportTypes::DI_XML => [
                'pattern' => [
                    'di.xml'
                ],
                'scanner' => new DiConfigScanner(new XmlRegistry(), $moduleNameResolver),
            ],
            ReportTypes::LAYOUT_XML => [
                'pattern' => [
                    '/view/*/*.xml'
                ],
                'scanner' => new LayoutConfigScanner(new XmlRegistry(), $moduleNameResolver),
            ],
            ReportTypes::SYSTEM_XML => [
                'pattern' => [
                    'system.xml'
                ],
                'scanner' => new SystemXmlScanner(new XmlRegistry(), $moduleNameResolver),
            ],
            ReportTypes::XSD => [
                'pattern' => [
                    '*.xsd'
                ],
                'scanner' => new XsdScanner(new XmlRegistry(), $moduleNameResolver),
            ],
            ReportTypes::LESS => [
                'pattern' => [
                    '*.less'
                ],
                'scanner' => new LessScanner(new LessRegistry(), new LessParser(), $moduleNameResolver),
            ]
        ];
    }
}
