<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Test\Vcs;

/**
 * @api
 */
trait ApiTrait
{
    use BaseTrait; //TODO Find out why we don't get the fully qualified name of this fella

    /**
     * @param array $methodParam
     */
    public function testExistingApiMethod(array $methodParam)
    {
    }
}
