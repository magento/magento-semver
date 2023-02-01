<?php

namespace Magento\SemanticVersionChecker\Reporter;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;

class HtmlTargetDecoratorTest extends TestCase
{
    /**
     * @dataProvider dataProviderTestUrl
     */
    public function testUrl(bool $hasOption, string $target, string $context, string $urlJson, string $expected): void
    {
        $input = $this->getMockBuilder(InputInterface::class)->getMockForAbstractClass();
        $input->expects($this->once())->method('hasOption')->with('report-html-target-url')->willReturn($hasOption);
        if ($hasOption) {
            $input->expects($this->once())->method('getOption')->with('report-html-target-url')->willReturn($urlJson);
        } else {
            $input->expects($this->never())->method('getOption');
        }
        $result = HtmlTargetDecorator::url($target, $context, $input);
        $this->assertEquals($expected, $result);
    }

    public function dataProviderTestUrl()
    {
        return [
            'target-context-class' => [
                true,
                'Magento\Framework\Registry',
                'class',
                '[{"reportTypes": ["interface", "class"], "url": "https://localhost/?target=%s"}]',
                '<a href="https://localhost/?target=TWFnZW50b1xGcmFtZXdvcmtcUmVnaXN0cnk=" target="_blank">Magento\Framework\Registry</a>'
            ],
            'target-context-class-array' => [
                true,
                'Magento\Framework\Registry',
                'class',
                '[{"reportTypes": ["mftf"], "url": "https://localhost/?target=%s"}, {"reportTypes": ["class"], "url": "https://localhost/?target=%s"}]',
                '<a href="https://localhost/?target=TWFnZW50b1xGcmFtZXdvcmtcUmVnaXN0cnk=" target="_blank">Magento\Framework\Registry</a>'
            ],
            'target-context-mftf' => [
                true,
                'Magento\Framework\Registry',
                'mftf',
                '[{"reportTypes": ["interface", "class"], "url": "https://localhost/?target=%s"}]',
                'Magento\Framework\Registry'
            ],
            'empty-json' => [
                true,
                'Magento\Framework\Registry::$someProperty',
                'class',
                '',
                'Magento\Framework\Registry::$someProperty'
            ],
            'broken-json' => [
                true,
                'Magento\Framework\Registry',
                'class',
                '[{"reportTypes": ["interface", "class"]',
                'Magento\Framework\Registry'
            ],
            'has-no-option' => [
                false,
                'Magento\Framework\Registry',
                'class',
                '',
                'Magento\Framework\Registry'
            ],
        ];
    }
}
