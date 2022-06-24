<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Test\Vcs {

    use Foo\Bar\Baz;

    /**
     * @api
     */
    trait TestTrait
    {
        /**
         * Demonstrates what happens when native return type hint in a public method is moved.
         *
         * @param int $int
         */
        public function movedNativeTypePublic($int): int
        {
            return $int;
        }

        /**
         * Demonstrates what happens when non native return type hint in a public method is moved.
         *
         * @param Baz $object
         */
        public function movedNonNativeTypePublic($object): Baz
        {
            return $object;
        }

        /**
         * Demonstrates what happens when native return type hint in a protected method is moved.
         *
         * @param int $int
         */
        protected function movedNativeTypeProtected($int): int
        {
            return $int;
        }

        /**
         * Demonstrates what happens when native return type hint in a private method is moved.
         *
         * @param int $int
         */
        private function movedNativeTypePrivate($int): int
        {
            return $int;
        }
    }
}

namespace Foo\Bar {
    class Baz
    {
    }
}
