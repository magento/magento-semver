<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Test\Vcs;

/**
 * @api
 */
trait TestTrait
{
    /**
     * @param array $int
     */
    public function declarationAddedPublic($int)
    {
        return $int;
    }

    public function annotationAddedPublic($int)
    {
        return $int;
    }

    /**
     * @param array $int
     */
    protected function declarationAddedProtected($int)
    {
        return $int;
    }

    protected function annotationAddedProtected($int)
    {
        return $int;
    }

    /**
     * @param array $int
     */
    private function declarationAddedPrivate($int)
    {
        return $int;
    }

    private function annotationAddedPrivate($int)
    {
        return $int;
    }

    /**
     * @param array $int
     */
    public function declarationChangedPublic($int): array
    {
        return $int;
    }

    /**
     * @param array $int
     * @return array
     */
    public function annotationChangedPublic($int)
    {
        return $int;
    }

    /**
     * @param array $int
     */
    protected function declarationChangedProtected($int): array
    {
        return $int;
    }

    /**
     * @param array $int
     * @return array
     */
    protected function annotationChangedProtected($int)
    {
        return $int;
    }

    /**
     * @param array $int
     */
    private function declarationChangedPrivate($int): array
    {
        return $int;
    }

    /**
     * @param array $int
     * @return array
     */
    private function annotationChangedPrivate($int)
    {
        return $int;
    }

    /**
     * @param array $int
     */
    public function declarationRemovedPublic($int): array
    {
        return $int;
    }

    /**
     * @param array $int
     * @return array
     */
    public function annotationRemovedPublic($int)
    {
        return $int;
    }

    /**
     * @param array $int
     */
    protected function declarationRemovedProtected($int): array
    {
        return $int;
    }

    /**
     * @param array $int
     * @return array
     */
    protected function annotationRemovedProtected($int)
    {
        return $int;
    }

    /**
     * @param array $int
     */
    private function declarationRemovedPrivate($int): array
    {
        return $int;
    }

    /**
     * @param array $int
     * @return array
     */
    private function annotationRemovedPrivate($int)
    {
        return $int;
    }

    public function php7RemoveAnnotationWithoutDoc(int $int1, int $int2) : int
    {
        return $int1 + $int2;
    }
}
