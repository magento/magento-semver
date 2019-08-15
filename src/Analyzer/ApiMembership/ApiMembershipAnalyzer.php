<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Analyzer\ApiMembership;

use PHPSemVerChecker\Registry\Registry;
use PHPSemVerChecker\Report\Report;

class ApiMembershipAnalyzer
{
    /**
     * @var Report
     */
    private $changeReport;

    /**
     * @var Report
     */
    private $membershipReport;

    /**
     * Compare with a destination registry (what the new source code is like).
     *
     * Separates entries for existing items that gained or lost the @api annotation
     *
     * @param Registry $apiRegistryBefore
     * @param Registry $apiRegistryAfter
     * @param Registry $fullRegistryBefore
     * @param Registry $fullRegistryAfter
     * @return void
     */
    public function analyzeWithMembership(
        $apiRegistryBefore,
        $apiRegistryAfter,
        $fullRegistryBefore,
        $fullRegistryAfter
    ) {
        $this->changeReport = new Report();
        $this->membershipReport = new Report();

        $analyzers = [
            new ClassApiMembershipAnalyzer(),
            new InterfaceApiMembershipAnalyzer()
        ];

        /** @var ApiMembershipAnalyzerTrait $analyzer */
        foreach ($analyzers as $analyzer) {
            $analyzer->analyzeWithMembership(
                $apiRegistryBefore,
                $apiRegistryAfter,
                $fullRegistryBefore,
                $fullRegistryAfter
            );
            $this->changeReport->merge($analyzer->getBreakingChangeReport());
            $this->membershipReport->merge($analyzer->getApiMembershipReport());
        }
    }

    /**
     * Get the report of changes made to the existing APIs
     *
     * @return Report
     */
    public function getBreakingChangeReport()
    {
        return $this->changeReport;
    }

    /**
     * Get the report of changes made to API membership (items that gained/lost API status)
     *
     * @return Report
     */
    public function getApiMembershipReport()
    {
        return $this->membershipReport;
    }
}
