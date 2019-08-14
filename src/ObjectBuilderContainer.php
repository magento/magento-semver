<?php


namespace Magento\SemanticVersionChecker;


use Magento\SemanticVersionChecker\Scanner\DbSchemaScannerDecorator;
use Magento\SemanticVersionChecker\Scanner\Scanner;
use Magento\SemanticVersionChecker\Visitor\ApiClassVisitor;
use Magento\SemanticVersionChecker\Visitor\ApiInterfaceVisitor;
use PhpParser\Lexer\Emulative;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser\Php7 as Parser;
use PHPSemVerChecker\Registry\Registry;
use PHPSemVerChecker\Visitor\ClassVisitor;
use PHPSemVerChecker\Visitor\FunctionVisitor;
use PHPSemVerChecker\Visitor\InterfaceVisitor;
use PHPSemVerChecker\Visitor\TraitVisitor;

class ObjectBuilderContainer
{

    public function getAllScanner(): array
    {
        // @todo use dependency injection
        $scanner = $this->buildFullScanner();
        $scannerApi = $this->buildApiScanner();
        // @todo refactoring should use own registry step 1 to get an working poc
        $scannerDb = new DbSchemaScannerDecorator($scanner->getRegistry());

        return [
            'all' => [
                'type' => 'php',
                'pattern' => [
                    '*.php',
                ],
                'scanner' => $scanner,
            ],
            'api' => [
                'type' => 'php',
                'pattern' => [
                    '*.php',
                ],
                'scanner' => $scannerApi,
            ],
            'dbSchema' => [
                'type' => 'xml',
                'pattern' => [
                    '*.xml',
                ],
                'scanner' => $scannerDb,
            ],
        ];
    }

    /**
     * @return Scanner
     * @todo use dependency injection
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
     * @todo use dependency injection
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
            new TraitVisitor($registry),
        ];

        return new Scanner($registry, $parser, $traverser, $apiVisitors);
    }
}
