<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Test\Vcs;

/**
 * Defines a trait that demonstrates that unchanged code does no trigger false positives.
 *
 * @api
 */
trait TestTrait
{
    /**
     * A test method that remains unchanged between iterations.
     *
     * @param string $input
     * @return string
     */
    private function testMethod(string $input): string
    {
        return trim($input);
    }
}
