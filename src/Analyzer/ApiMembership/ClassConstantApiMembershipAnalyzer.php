<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionCheckr\Analyzer\ApiMembership;

use Magento\SemanticVersionCheckr\Analyzer\ClassConstantAnalyzer;

/**
 * Class constant analyzer.
 * Performs comparison of changed constants and creates reports such as:
 * - class constants moved to parent
 * - class constants removed
 * - class constants added
 *
 * The API membership report contains:
 * - Constants that still exist but have been removed from the API
 * - Constants that already exist but have been added to the API
 */
class ClassConstantApiMembershipAnalyzer extends ClassConstantAnalyzer
{
    use ApiMembershipAnalyzerTrait;
}
