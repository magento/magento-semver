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
         * Demonstrates what happens when native parameter type hint is moved.
         *
         * @param $int
         * @return int
         */
        public function movedNativeType(int $int);

        /**
         * Demonstrates what happens when non native parameter type hint is moved.
         *
         * @param $object
         * @return BazInterface
         */
        public function movedNonNativeType(BazInterface $object);
    }
}

namespace Foo\Bar {
    interface BazInterface
    {
    }
}
