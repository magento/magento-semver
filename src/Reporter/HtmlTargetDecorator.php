<?php

namespace Magento\SemanticVersionChecker\Reporter;

use Symfony\Component\Console\Input\InputInterface;

/**
 * Html decorator for Target field of the report
 */
class HtmlTargetDecorator
{
    /**
     * Create a link tag for specific report types
     *
     * @param string $target
     * @param string $context
     * @param InputInterface $input
     * @return string
     */
    public static function url(string $target, string $context, InputInterface $input): string
    {
        $urlContextJson = $input->getOption('report-html-target-url');
        foreach (@json_decode($urlContextJson, true) ?? [] as $urlContext) {
            if (!in_array($context, $urlContext['reportTypes']) || !$urlContext['url']) {
                continue;
            }
            $href = sprintf($urlContext['url'], base64_encode($target));
            $target = sprintf('<a href="%s" target="_blank">%s</a>', $href, $target);
        }
        return $target;
    }
}
