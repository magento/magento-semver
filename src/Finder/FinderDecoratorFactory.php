<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Finder;

class FinderDecoratorFactory
{
    /**
     * Builder to create a File FinderDecorator
     *
     * @return FinderDecorator
     */
    public function create(): FinderDecorator
    {
        return new FinderDecorator(
            [
                'db_schema.xml',
                'db_schema_whitelist.json',
                'di.xml',
                '/view/*/*.xml',
                '/etc/adminhtml/system.xml',
                '/etc/*.xsd',
                '/view/*/*/*/*.less',
            ],
            [
                'ui_component',
            ]
        );
    }
}
