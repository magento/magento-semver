<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tools\SemanticVersionChecker;

/**
 * Holds the different report type keys.
 */
class ReportTypes
{
    const ALL = 'all';
    const API = 'api';
    const DB_SCHEMA = 'dbSchema';
    const DI_XML = 'diXml';
    const LAYOUT_XML = 'layout';
}
