<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker;

use Magento\SemanticVersionChecker\Analyzer\EtSchemaAnalyzer;
/**
 * Holds the different report type keys.
 */
class ReportTypes
{
    public const ALL        = 'all';
    public const API        = 'api';
    public const DB_SCHEMA  = 'dbSchema';
    public const DI_XML     = 'diXml';
    public const LAYOUT_XML = 'layout';
    public const SYSTEM_XML = 'systemXml';
    public const XSD        = 'xsd';
    public const LESS       = 'less';
    public const ET_SCHEMA  = EtSchemaAnalyzer::CONTEXT;
}
