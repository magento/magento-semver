<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Scanner;

use PhpParser\Error;
use PhpParser\Lexer\Emulative;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser\Php7 as Parser;
use PHPSemVerChecker\Registry\Registry;
use Magento\SemanticVersionChecker\Visitor\ApiClassVisitor;
use PHPSemVerChecker\Visitor\ClassVisitor;
use PHPSemVerChecker\Visitor\FunctionVisitor;
use Magento\SemanticVersionChecker\Visitor\ApiInterfaceVisitor;
use PHPSemVerChecker\Visitor\InterfaceVisitor;
use PHPSemVerChecker\Visitor\TraitVisitor;
use RuntimeException;

class Scanner
{
    /**
     * @var \PHPSemVerChecker\Registry\Registry
     */
    protected $registry;

    /**
     * @var \PhpParser\Parser
     */
    protected $parser;

    /**
     * @var \PhpParser\NodeTraverser
     */
    protected $traverser;

    /**
     * @param string $reportType "api|all"
     * @throws \Exception
     */
    public function __construct($reportType = 'all')
    {
        $this->registry = new Registry();
        $this->parser = new Parser(new Emulative());
        $this->traverser = new NodeTraverser();

        if ($reportType === 'all') {
            $classVisitor = new ClassVisitor($this->registry);
            $interfaceVisitor = new InterfaceVisitor($this->registry);
        } elseif ($reportType === 'api') {
            $classVisitor = new ApiClassVisitor($this->registry);
            $interfaceVisitor = new ApiInterfaceVisitor($this->registry);
        } else {
            throw new \Exception("Unexpected report type given: \"$reportType\"");
        }

        $visitors = [
            new NameResolver(),
            $classVisitor,
            $interfaceVisitor,
            new FunctionVisitor($this->registry),
            new TraitVisitor($this->registry),
        ];

        foreach ($visitors as $visitor) {
            $this->traverser->addVisitor($visitor);
        }
    }

    /**
     * @param string $file
     * @return void
     */
    public function scan($file)
    {
        // Set the current file used by the registry so that we can tell where the change was scanned.
        $this->registry->setCurrentFile($file);

        $code = file_get_contents($file);

        try {
            $statements = $this->parser->parse($code);
            $this->traverser->traverse($statements);
        } catch (Error $e) {
            throw new RuntimeException('Parse Error: ' . $e->getMessage() . ' in ' . $file);
        }
    }

    /**
     * @return \PHPSemVerChecker\Registry\Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }
}
