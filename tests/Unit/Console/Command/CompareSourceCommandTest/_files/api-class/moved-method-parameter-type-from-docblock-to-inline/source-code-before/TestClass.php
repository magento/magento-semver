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
    class TestClass
    {
        /**
         * Demonstrates what happens when native parameter type hint in a public method is moved.
         *
         * @param int $int
         * @return int
         */
        public function movedNativeTypePublic($int)
        {
            return $int;
        }

        /**
         * Demonstrates what happens when non native parameter type hint in a public method is moved.
         *
         * @param Baz $object
         * @return Baz
         */
        public function movedNonNativeTypePublic($object)
        {
            return $object;
        }

        /**
         * Demonstrates what happens when native parameter type hint in a protected method is moved.
         *
         * @param int $int
         * @return int
         */
        protected function movedNativeTypeProtected($int)
        {
            return $int;
        }

        /**
         * Demonstrates what happens when native parameter type hint in a private method is moved.
         *
         * @param int $int
         * @return int
         */
        private function movedNativeTypePrivate($int)
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
