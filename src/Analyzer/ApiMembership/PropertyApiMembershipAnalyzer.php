<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tools\SemanticVersionChecker\Analyzer\ApiMembership;

use Magento\Tools\SemanticVersionChecker\Analyzer\PropertyAnalyzer;

/**
 * Property analyzer with separate API membership report.
 *
 * Performs comparison of changed properties and creates reports such as:
 * - class property moved to parent
 * - class property removed
 * - class property added
 *
 * The API membership report contains:
 * - Properties that still exist but have been removed from the API
 * - Properties that already exist but have been added to the API
 */
class PropertyApiMembershipAnalyzer extends PropertyAnalyzer
{
    use ApiMembershipAnalyzerTrait;
}
