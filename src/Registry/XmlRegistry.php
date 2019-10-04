<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Registry;

use PHPSemVerChecker\Registry\Registry;

class XmlRegistry extends Registry
{
    /**
     *  A all the nodes that were found in the xml.
     * @var array
     */
    private $nodes = [];

    /**
     * Add a new xml node to the list.
     *
     * @param string $context
     * @param mixed $data
     */
    public function addXmlNode(string $context, $data): void
    {
        $this->nodes[$context][] = $data;
    }

    /**
     * Return all nodes that were found in the xml.
     * @return array
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }
}
