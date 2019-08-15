<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Analyzer\ApiMembership;

use PhpParser\Node;
use PHPSemVerChecker\Registry\Registry;
use PHPSemVerChecker\Report\Report;

/**
 * Trait to further extend AbstractCodeAnalyzer to separately handle adding or removing the @api annotation
 */
trait ApiMembershipAnalyzerTrait
{
    /** @var Report */
    protected $changeReport;

    /** @var Report */
    protected $membershipReport;

    /**
     * Analyzer context.
     *
     * @var null|string
     */
    protected $context;

    /**
     * File path before changes.
     *
     * @var null|string
     */
    protected $fileBefore;

    /**
     * File path after changes.
     *
     * @var null|string
     */
    protected $fileAfter;

    /**
     * @param string $context
     * @param string $fileBefore
     * @param string $fileAfter
     */
    public function __construct($context = null, $fileBefore = null, $fileAfter = null)
    {
        $this->context = $context;
        $this->fileBefore = $fileBefore;
        $this->fileAfter = $fileAfter;
        $this->changeReport = new Report();
        $this->membershipReport = new Report();
    }

    /**
     * Analyze the filtered registries or entries and load both general and API membership version reports
     *
     * @param Registry|Node $apiBefore
     * @param Registry|Node $apiAfter
     * @param Registry|Node $fullBefore
     * @param Registry|Node $fullAfter
     * @return void
     */
    public function analyzeWithMembership($apiBefore, $apiAfter, $fullBefore, $fullAfter)
    {
        $apiBeforeMap = $this->getNodeNameMap($apiBefore);
        $apiAfterMap = $this->getNodeNameMap($apiAfter);
        $fullBeforeMap = $this->getNodeNameMap($fullBefore);
        $fullAfterMap = $this->getNodeNameMap($fullAfter);

        $apiNamesBefore = array_keys($apiBeforeMap);
        $apiNamesAfter = array_keys($apiAfterMap);
        $fullNamesBefore = array_keys($fullBeforeMap);
        $fullNamesAfter = array_keys($fullAfterMap);

        $apiAdded = array_diff($apiNamesAfter, $apiNamesBefore);
        $fullyAdded = array_diff($apiNamesAfter, $fullNamesBefore);
        $membershipAdded = array_diff($apiAdded, $fullyAdded);

        $apiRemoved = array_diff($apiNamesBefore, $apiNamesAfter);
        $fullyRemoved = array_diff($apiNamesBefore, $fullNamesAfter);
        $membershipRemoved = array_diff($apiRemoved, $fullyRemoved);

        $toVerify = array_intersect($apiNamesBefore, $apiNamesAfter);

        $this->reportAdded($this->changeReport, $apiAfter, $fullyAdded);
        $this->reportAdded($this->membershipReport, $apiAfter, $membershipAdded);

        $this->reportMovedOrRemoved($this->changeReport, $apiBefore, $apiAfter, $fullyRemoved);
        $this->reportMovedOrRemoved($this->membershipReport, $apiBefore, $apiAfter, $membershipRemoved);

        $this->reportChangedWithMembership(
            $apiBefore,
            $apiAfter,
            $fullBefore,
            $fullAfter,
            $toVerify
        );
    }

    /**
     * Throw exception if the analyze() method from AnalyzerInterface is called instead of analyzeWithMembership()
     *
     * @param Registry $registryBefore
     * @param Registry $registryAfter
     * @return void
     * @throws \BadMethodCallException
     */
    public function analyze($registryBefore, $registryAfter)
    {
        throw new \BadMethodCallException('API Membership Analyzers use analyzeWithMembership() instead of analyze()');
    }

    /**
     * Get the version report for non-membership changes
     *
     * @return Report
     */
    public function getBreakingChangeReport()
    {
        return $this->changeReport;
    }

    /**
     * Get the version report for changes to API membership
     *
     * @return Report
     */
    public function getApiMembershipReport()
    {
        return $this->membershipReport;
    }

    /**
     * Find changes to nodes that exist in both before and after states and add them to the report
     *
     * @param Node|Registry $apiBefore
     * @param Node|Registry $apiAfter
     * @param Node|Registry $fullBefore
     * @param Node|Registry $fullAfter
     * @param string[] $toVerify
     * @return void
     */
    protected function reportChangedWithMembership($apiBefore, $apiAfter, $fullBefore, $fullAfter, $toVerify)
    {
        // Normally call the non-membership method because changes to class/interface members are not reported on the
        // membership report, but Class/Interface membership analyzers need to override this to call their
        // child API membership analyzers so add/removes of children will make it on the membership report
        $this->reportChanged($this->changeReport, $apiBefore, $apiAfter, $toVerify);
    }

    /**
     * Gets the appropriate nodes from the context and maps them to their names
     *
     * If the user of the trait extends AbstractCodeAnalyzer, it does not also need to re-implement this function
     *
     * @param Node|Registry $context
     * @return Node[]
     */
    abstract protected function getNodeNameMap($context);

    /**
     * Report the list of added nodes
     *
     * If the user of the trait extends AbstractCodeAnalyzer, it does not also need to re-implement this function
     *
     * @param Report $report
     * @param Node|Registry $contextAfter
     * @param string[] $addedNames
     * @return void
     */
    abstract protected function reportAdded($report, $contextAfter, $addedNames);

    /**
     * Report moved or removed nodes
     *
     * If the user of the trait extends AbstractCodeAnalyzer, it does not also need to re-implement this function
     *
     * @param Report $report
     * @param Node|Registry $contextBefore
     * @param Node|Registry $contextAfter
     * @param string[] $removedNames
     * @return void
     */
    abstract protected function reportMovedOrRemoved($report, $contextBefore, $contextAfter, $removedNames);

    /**
     * Find changes to nodes that exist in both before and after states and add them to the report
     *
     * If the user of the trait extends AbstractCodeAnalyzer, it does not also need to re-implement this function
     *
     * @param Report $report
     * @param Node|Registry $contextBefore
     * @param Node|Registry $contextAfter
     * @param string[] $toVerify
     * @return void
     */
    abstract protected function reportChanged($report, $contextBefore, $contextAfter, $toVerify);
}
