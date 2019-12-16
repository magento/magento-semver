<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Test\Vcs {

    use Foo\Bar\Test;
    use Foo\Buzz\Baz;

    /**
     * @api
     */
    class TestClass
    {
        /**
         * @param int $int
         */
        public function declarationAddedPublic($int): ?int
        {
            return $int;
        }

        /**
         * @param int $int
         * @return int|null
         */
        public function annotationAddedPublic($int)
        {
            return $int;
        }

        /**
         * @param int $int
         */
        protected function declarationAddedProtected($int): ?int
        {
            return $int;
        }

        /**
         * @param int $int
         * @return int|null
         */
        protected function annotationAddedProtected($int)
        {
            return $int;
        }

        /**
         * @param int $int
         */
        private function declarationAddedPrivate($int): ?int
        {
            return $int;
        }

        /**
         * @param int $int
         * @return int|null
         */
        private function annotationAddedPrivate($int)
        {
            return $int;
        }

        /**
         * @param int $int
         */
        public function declarationChangedPublic($int): ?int
        {
            return $int;
        }

        /**
         * @param int $int
         * @return int|null
         */
        public function annotationChangedPublic($int)
        {
            return $int;
        }

        /**
         * @param int $int
         */
        protected function declarationChangedProtected($int): ?int
        {
            return $int;
        }

        /**
         * @param int $int
         * @return int|null
         */
        protected function annotationChangedProtected($int)
        {
            return $int;
        }

        /**
         * @param int $int
         */
        private function declarationChangedPrivate($int): ?int
        {
            return $int;
        }

        /**
         * @param int $int
         * @return int|null
         */
        private function annotationChangedPrivate($int)
        {
            return $int;
        }

        /**
         * @param int $int
         */
        public function declarationRemovedPublic($int)
        {
            return $int;
        }

        /**
         * @param int $int
         */
        public function annotationRemovedPublic($int)
        {
            return $int;
        }

        /**
         * @param int $int
         */
        protected function declarationRemovedProtected($int)
        {
            return $int;
        }

        /**
         * @param int $int
         */
        protected function annotationRemovedProtected($int)
        {
            return $int;
        }

        /**
         * @param int $int
         */
        private function declarationRemovedPrivate($int)
        {
            return $int;
        }

        /**
         * @param int $int
         */
        private function annotationRemovedPrivate($int)
        {
            return $int;
        }

        public function php7RemoveAnnotationWithoutDoc(int $int1, int $int2)
        {
            return $int1 + $int2;
        }

        public function classReturnTypeInlineDeclaration(): Test
        {
            return new Test();
        }

        public function nullableClassReturnTypeInlineDeclaration(): ?Test
        {
            return null;
        }

        public function toBeChangedClassReturnTypeInlineDeclaration(): Baz
        {
            return new \Foo\Buzz\Baz();
        }

        public function nullableToBeChangedClassReturnTypeInlineDeclaration(): ?Baz
        {
            return null;
        }
    }
}

namespace Foo\Bar {
    class Test
    {
    }

    class Baz
    {
    }
}

namespace Foo\Buzz {
    class Baz
    {
    }
}
