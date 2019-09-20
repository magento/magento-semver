<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Analyzer\ApiMembership;

use Magento\SemanticVersionChecker\Analyzer\ClassMethodAnalyzer;

/**
 * Class method analyzer with separate API membership report.
 *
 * Performs comparison of changed methods and creates reports such as:
 * - class method moved to parent
 * - class method removed
 * - class method added
 *
 * The API membership report contains:
 * - Methods that still exist but have been removed from the API
 * - Methods that already exist but have been added to the API
 */
class ClassMethodApiMembershipAnalyzer extends ClassMethodAnalyzer
{
    use ApiMembershipAnalyzerTrait;
}
