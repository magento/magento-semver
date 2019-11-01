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
         * @var int $int
         * @return int
         */
        public function movedNativeType($int);

        /**
         * Demonstrates what happens when non native variable type hint is moved.
         *
         * @var BazInterface $object
         * @return Baz
         */
        public function movedNonNativeType($object);
    }
}

namespace Foo\Bar {
    interface BazInterface
    {
    }
}
