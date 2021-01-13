<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Registry;

use Magento\SemanticVersionChecker\Node\Layout\Container;
use Magento\SemanticVersionChecker\Node\Layout\LayoutNodeInterface;
use PHPSemVerChecker\Registry\Registry;

class XmlRegistry extends Registry
{
    /**
     * Defines the key for nodes in the data array.
     */
    public const NODES_KEY = 'nodes';

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
     * Add layout container node to mapping and data
     *
     * @param LayoutNodeInterface $layoutNode
     * @param string $moduleName
     */
    public function addLayoutContainerNode(LayoutNodeInterface $layoutNode, string $moduleName) {
        $this->data[self::NODES_KEY][$moduleName][$layoutNode->getUniqueKey()] = $layoutNode;
        $this->mapping[self::NODES_KEY][$moduleName][$layoutNode->getUniqueKey()] = $this->getCurrentFile();
    }

    /**
     * Get the corresponding file given the module name and layoutNode key
     *
     * @param string $moduleName
     * @param string $uniqueKey
     * @return mixed
     */
    public function getLayoutFile(string $moduleName, string $uniqueKey) {
        return $this->mapping[self::NODES_KEY][$moduleName][$uniqueKey];
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
