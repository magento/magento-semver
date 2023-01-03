<?php

namespace Magento\SemanticVersionChecker\Reporter;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;

class HtmlTargetDecoratorTest extends TestCase
{
    /**
     * @dataProvider dataProviderTestUrl
     */
    public function testUrl(string $target, string $context, string $urlJson, string $expected): void
    {
        $input = $this->getMockBuilder(InputInterface::class)->getMockForAbstractClass();
        $input->expects($this->once())->method('getOption')->with('report-html-target-url')->willReturn($urlJson);
        $result = HtmlTargetDecorator::url($target, $context, $input);
        $this->assertEquals($expected, $result);
    }

    public function dataProviderTestUrl()
    {
        return [
            'target-context-class' => [
                'Magento\Framework\Registry',
                'class',
                '[{"reportTypes": ["interface", "class"], "url": "https://localhost/?target=%s"}]',
                '<a href="https://localhost/?target=TWFnZW50b1xGcmFtZXdvcmtcUmVnaXN0cnk=" target="_blank">Magento\Framework\Registry</a>'
            ],
            'target-context-class-array' => [
                'Magento\Framework\Registry',
                'class',
                '[{"reportTypes": ["mftf"], "url": "https://localhost/?target=%s"}, {"reportTypes": ["class"], "url": "https://localhost/?target=%s"}]',
                '<a href="https://localhost/?target=TWFnZW50b1xGcmFtZXdvcmtcUmVnaXN0cnk=" target="_blank">Magento\Framework\Registry</a>'
            ],
            'target-context-mftf' => [
                'Magento\Framework\Registry',
                'mftf',
                '[{"reportTypes": ["interface", "class"], "url": "https://localhost/?target=%s"}]',
                'Magento\Framework\Registry'
            ],
            'empty-json' => [
                'Magento\Framework\Registry',
                'class',
                '',
                'Magento\Framework\Registry'
            ],
            'broken-json' => [
                'Magento\Framework\Registry',
                'class',
                '[{"reportTypes": ["interface", "class"]',
                'Magento\Framework\Registry'
            ],
        ];
    }
}
