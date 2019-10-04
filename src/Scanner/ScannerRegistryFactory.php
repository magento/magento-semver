<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tools\SemanticVersionChecker\Scanner;

use Magento\Tools\SemanticVersionChecker\Registry\XmlRegistry;
use Magento\Tools\SemanticVersionChecker\ReportBuilder;
use Magento\Tools\SemanticVersionChecker\ReportTypes;
use Magento\Tools\SemanticVersionChecker\Visitor\ApiClassVisitor;
use Magento\Tools\SemanticVersionChecker\Visitor\ApiInterfaceVisitor;
use Magento\Tools\SemanticVersionChecker\Visitor\ApiTraitVisitor;
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
        $registry = new Registry();
        $parser = new Parser(new Emulative());
        $traverser = new NodeTraverser();

        $classVisitor = new ClassVisitor($registry);
        $interfaceVisitor = new InterfaceVisitor($registry);
        $apiVisitors = [
            new NameResolver(),
            $classVisitor,
            $interfaceVisitor,
            new FunctionVisitor($registry),
            new TraitVisitor($registry),
        ];

        return new Scanner($registry, $parser, $traverser, $apiVisitors);
    }

    /**
     * @return Scanner
     */
    private function buildApiScanner()
    {
        $registry = new Registry();
        $parser = new Parser(new Emulative());
        $traverser = new NodeTraverser();

        $classVisitor = new ApiClassVisitor($registry);
        $interfaceVisitor = new ApiInterfaceVisitor($registry);
        $apiVisitors = [
            new NameResolver(),
            $classVisitor,
            $interfaceVisitor,
            new FunctionVisitor($registry),
            new ApiTraitVisitor($registry),
        ];

        return new Scanner($registry, $parser, $traverser, $apiVisitors);
    }

    public function create()
    {
        $registry = new XmlRegistry();
        $getModuleNameByPath =  new ModuleNamespaceResolver();

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
                'scanner' => $this->buildApiScanner(),
            ],
            ReportTypes::DB_SCHEMA => [
                'pattern' => [
                    'db_schema.xml',
                    'db_schema_whitelist.json',
                ],
                'scanner' => new DbSchemaScanner($registry, $getModuleNameByPath),
            ],
            ReportTypes::DI_XML => [
                'pattern' => [
                    'di.xml'
                ],
                'scanner' => new DiConfigScanner(new XmlRegistry(), $getModuleNameByPath),
            ],
            ReportTypes::LAYOUT_XML => [
                'pattern' => [
                    '/view/*/*.xml'
                ],
                'scanner' => new LayoutConfigScanner(new XmlRegistry(), $getModuleNameByPath),
            ]
        ];
    }
}
