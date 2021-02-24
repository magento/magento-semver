<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Operation;

/**
 * When @api annotation has been added
 */
class ClassLikeApiAnnotationAdded extends ClassLikeApiAnnotationOperation
{
    /**
     * @var string
     */
    protected $code = 'M0141';

    /**
     * @var string
     */
    protected $reason = '@api annotation has been added.';
}
