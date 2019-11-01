<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionCheckr\Parser;

use Less_Parser;

/**
 * Extension of {@link \Less_Parser} to provide public access to parsed less rules.
 */
class LessParser extends Less_Parser
{
    /**
     * @return array
     */
    public function getRules(): array
    {
        return $this->rules;
    }
}
