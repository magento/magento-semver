<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Test\Vcs {

    use Foo\Bar\BazInterface;

    /**
     * @api
     */
    interface TestInterface
    {
        /**
         * Demonstrates what happens when native return type hint is moved.
         *
         * @param int $int
         */
        public function movedNativeType($int): int;

        /**
         * Demonstrates what happens when non native return type hint is moved.
         *
         * @param BazInterface $object
         */
        public function movedNonNativeType($object): BazInterface;
    }
}

namespace Foo\Bar {
    interface BazInterface
    {
    }
}
