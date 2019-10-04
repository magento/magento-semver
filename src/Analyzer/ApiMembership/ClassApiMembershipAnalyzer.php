<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tools\SemanticVersionChecker\Analyzer\ApiMembership;

use Magento\Tools\SemanticVersionChecker\Analyzer\ClassAnalyzer;
use PHPSemVerChecker\Registry\Registry;

/**
 * Class analyzer with separate API membership report.
 *
 * Performs comparison of classes and creates reports such as:
 * - Class added
 * - Class removed
 *
 * The API membership report contains:
 * - Classes that still exist but have been removed from the API
 * - Classes that already exist but have been added to the API
 *
 * Runs method, property, and constant analyzers.
 */
class ClassApiMembershipAnalyzer extends ClassAnalyzer
{
    use ApiMembershipAnalyzerTrait;

    /**
     * Find changes to classes that exist in both before and after states and add them to the report
     *
     * @param Registry $apiBefore
     * @param Registry $apiAfter
     * @param Registry $fullBefore
     * @param Registry $fullAfter
     * @param string[] $toVerify
     * @return void
     */
    protected function reportChangedWithMembership($apiBefore, $apiAfter, $fullBefore, $fullAfter, $toVerify)
    {
        $apiBeforeMap = $this->getNodeNameMap($apiBefore);
        $apiAfterMap = $this->getNodeNameMap($apiAfter);
        $fullBeforeMap = $this->getNodeNameMap($fullBefore);
        $fullAfterMap = $this->getNodeNameMap($fullAfter);
        foreach ($toVerify as $className) {
            $fileBefore = $apiBefore->mapping[static::CONTEXT][$className];
            $fileAfter = $apiAfter->mapping[static::CONTEXT][$className];

            $apiClassBefore = $apiBeforeMap[$className];
            $apiClassAfter = $apiAfterMap[$className];

            if ($apiClassBefore !== $apiClassAfter) {
                $fullClassBefore = $fullBeforeMap[$className];
                $fullClassAfter = $fullAfterMap[$className];

                /**
                 * @var ApiMembershipAnalyzerTrait[] $analyzers
                 */
                $analyzers = [
                    new ClassMethodApiMembershipAnalyzer(static::CONTEXT, $fileBefore, $fileAfter),
                    new PropertyApiMembershipAnalyzer(static::CONTEXT, $fileBefore, $fileAfter),
                    new ClassConstantApiMembershipAnalyzer(static::CONTEXT, $fileBefore, $fileAfter)
                ];

                foreach ($analyzers as $analyzer) {
                    $analyzer->analyzeWithMembership(
                        $apiClassBefore,
                        $apiClassAfter,
                        $fullClassBefore,
                        $fullClassAfter
                    );
                    $this->changeReport->merge($analyzer->getBreakingChangeReport());
                    $this->membershipReport->merge($analyzer->getApiMembershipReport());
                }
            }
        }
    }
}
