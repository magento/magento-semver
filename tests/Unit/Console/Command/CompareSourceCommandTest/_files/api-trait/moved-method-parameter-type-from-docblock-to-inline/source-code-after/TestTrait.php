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
         * Demonstrates what happens when native parameter type hint in a public method is moved.
         *
         * @param $int
         * @return int
         */
        public function movedNativeTypePublic(int $int)
        {
            return $int;
        }

        /**
         * Demonstrates what happens when non native parameter type hint in a public method is moved.
         *
         * @param $object
         * @return Baz
         */
        public function movedNonNativeTypePublic(Baz $object)
        {
            return $object;
        }

        /**
         * Demonstrates what happens when native parameter type hint in a protected  method is moved.
         *
         * @param $int
         * @return int
         */
        protected function movedNativeTypeProtected(int $int)
        {
            return $int;
        }

        /**
         * Demonstrates what happens when native parameter type hint in a private method is moved.
         *
         * @param $int
         * @return int
         */
        private function movedNativeTypePrivate(int $int)
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
