<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tools\SemanticVersionChecker\Analyzer\ApiMembership;

use Magento\Tools\SemanticVersionChecker\Analyzer\InterfaceAnalyzer;
use PHPSemVerChecker\Registry\Registry;

/**
 * Interface analyzer with separate API membership report.
 *
 * Performs comparison of interfaces and creates reports such as:
 * - Interface added
 * - Interface removed
 *
 * The API membership report contains:
 * - Interfaces that still exist but have been removed from the API
 * - Interfaces that already exist but have been added to the API
 *
 * Runs method and constant analyzers.
 */
class InterfaceApiMembershipAnalyzer extends InterfaceAnalyzer
{
    use ApiMembershipAnalyzerTrait;

    /**
     * Find changes to interfaces that exist in both before and after states and add them to the report
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
        foreach ($toVerify as $interfaceName) {
            $fileBefore = $apiBefore->mapping[static::CONTEXT][$interfaceName];
            $fileAfter = $apiAfter->mapping[static::CONTEXT][$interfaceName];

            $apiInterfaceBefore = $apiBeforeMap[$interfaceName];
            $apiInterfaceAfter = $apiAfterMap[$interfaceName];

            if ($apiInterfaceBefore !== $apiInterfaceAfter) {
                $fullInterfaceBefore = $fullBeforeMap[$interfaceName];
                $fullInterfaceAfter = $fullAfterMap[$interfaceName];

                /**
                 * @var ApiMembershipAnalyzerTrait[] $analyzers
                 */
                $analyzers = [
                    new ClassMethodApiMembershipAnalyzer(static::CONTEXT, $fileBefore, $fileAfter),
                    new ClassConstantApiMembershipAnalyzer(static::CONTEXT, $fileBefore, $fileAfter)
                ];

                foreach ($analyzers as $analyzer) {
                    $analyzer->analyzeWithMembership(
                        $apiInterfaceBefore,
                        $apiInterfaceAfter,
                        $fullInterfaceBefore,
                        $fullInterfaceAfter
                    );
                    $this->changeReport->merge($analyzer->getBreakingChangeReport());
                    $this->membershipReport->merge($analyzer->getApiMembershipReport());
                }
            }
        }
    }
}
