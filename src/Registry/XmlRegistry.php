<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionCheckr\Registry;

use PHPSemVerChecker\Registry\Registry;

class XmlRegistry extends Registry
{
    /**
     * Defines the key for nodes in the data array.
     */
    const NODES_KEY = 'nodes';

    /**
     * Add a new xml node to the list.
     *
     * @param string $context
     * @param mixed $data
     */
    public function addXmlNode(string $context, $data): void
    {
        $this->data[self::NODES_KEY][$context][] = $data;
    }

    /**
     * Return all nodes that were found in the xml.
     * @return array
     */
    public function getNodes(): array
    {
        return $this->data[self::NODES_KEY] ?? [];
    }
}
