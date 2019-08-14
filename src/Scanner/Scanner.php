<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Scanner;

use PhpParser\Error;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\Parser\Php7 as Parser;
use PHPSemVerChecker\Registry\Registry;
use RuntimeException;

class Scanner implements ScannerInterface
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var \PhpParser\Parser
     */
    protected $parser;

    /**
     * @var NodeTraverser
     */
    protected $traverser;

    /**
     * @param Registry $registry
     * @param Parser $parser
     * @param NodeTraverser $nodeTraverser
     * @param NodeVisitor[] $vistors
     */
    public function __construct(Registry $registry, Parser $parser, NodeTraverser $traverser, array $visitors)
    {
        $this->registry = $registry;
        $this->parser = $parser;
        $this->traverser = $traverser;
        foreach ($visitors as $visitor) {
            $this->traverser->addVisitor($visitor);
        }
    }

    /**
     * @param string $file
     * @return void
     */
    public function scan(string $file): void
    {
        // Set the current file used by the registry so that we can tell where the change was scanned.
        $this->registry->setCurrentFile($file);
        $code = file_get_contents($file);

        try {
            $statements = $this->parser->parse($code);
            $this->traverser->traverse($statements);
        } catch (Error $e) {
            throw new RuntimeException('Parse Error: '.$e->getMessage().' in '.$file);
        }
    }

    /**
     * @return Registry
     */
    public function getRegistry(): Registry
    {
        return $this->registry;
    }
}
