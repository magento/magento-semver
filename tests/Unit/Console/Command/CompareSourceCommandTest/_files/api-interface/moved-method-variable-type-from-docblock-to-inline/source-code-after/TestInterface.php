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
         * Demonstrates what happens when native variable type hint is moved.
         *
         * @var $int
         * @return int
         */
        public function movedNativeType(int $int);

        /**
         * Demonstrates what happens when non native variable type hint is moved.
         *
         * @var $object
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
