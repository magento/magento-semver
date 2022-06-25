<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Helper;

use PHPUnit\Framework\TestCase;
use Magento\SemanticVersionChecker\Helper\ClassParser;

class ClassParserTest extends TestCase
{
    public function testExtendsAlias()
    {
        $path = __DIR__ . '/_files/ClassExtendAlias.php';
        $parser = new ClassParser($path);
        $this->assertEquals('Test\VcsA\ClassA', $parser->getParentFullClassName());
    }

    public function testExtendsFull()
    {
        $path = __DIR__ . '/_files/ClassExtendFull.php';
        $parser = new ClassParser($path);
        $this->assertEquals('Test\VcsA\ClassA', $parser->getParentFullClassName());
    }

    public function testImplementsAlias()
    {
        $path = __DIR__ . '/_files/ClassExtendAlias.php';
        $parser = new ClassParser($path);
        $result = $parser->getImplementedInterfacesNames();
        $this->assertCount(1, $result);
        $this->assertContains('Test\VcsA\A\InterfaceA', $parser->getImplementedInterfacesNames());
    }

    public function testImplementsFull()
    {
        $path = __DIR__ . '/_files/ClassExtendFull.php';
        $parser = new ClassParser($path);
        $result = $parser->getImplementedInterfacesNames();
        $this->assertCount(1, $result);
        $this->assertContains('Test\VcsA\A\InterfaceA', $parser->getImplementedInterfacesNames());
    }
}
