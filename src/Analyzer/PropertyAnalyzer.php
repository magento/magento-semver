<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Analyzer;

use Magento\SemanticVersionChecker\Operation\PropertyMoved;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PHPSemVerChecker\Operation\PropertyAdded;
use PHPSemVerChecker\Operation\PropertyRemoved;
use PHPSemVerChecker\Report\Report;

/**
 * Property analyzer.
 * Performs comparison of changed properties and creates reports such as:
 * - class property moved to parent
 * - class property removed
 * - class property added
 */
class PropertyAnalyzer extends AbstractCodeAnalyzer
{
    /**
     * Get the name of a Property node
     *
     * @param Property $property
     * @return string
     */
    protected function getNodeName($property)
    {
        return $property->props[0]->name;
    }

    /**
     * Use nodes of the Property type for this analyzer
     *
     * @return string
     */
    protected function getNodeClass()
    {
        return Property::class;
    }

    /**
     * Create and report a PropertyAdded operation
     *
     * @param Report $report
     * @param string $fileAfter
     * @param ClassLike $classAfter
     * @param Property $propertyAfter
     * @return void
     */
    protected function reportAddedNode($report, $fileAfter, $classAfter, $propertyAfter)
    {
        $report->add($this->context, new PropertyAdded($this->context, $fileAfter, $classAfter, $propertyAfter));
    }

    /**
     * Create and report a PropertyRemoved operation
     *
     * @param Report $report
     * @param string $fileBefore
     * @param ClassLike $classBefore
     * @param Property $propertyBefore
     * @return void
     */
    protected function reportRemovedNode($report, $fileBefore, $classBefore, $propertyBefore)
    {
        $report->add($this->context, new PropertyRemoved($this->context, $fileBefore, $classBefore, $propertyBefore));
    }

    /**
     * Create and report a PropertyMoved operation
     *
     * @param Report $report
     * @param string $fileBefore
     * @param ClassLike $classBefore
     * @param Property $propertyBefore
     * @return void
     */
    protected function reportMovedNode($report, $fileBefore, $classBefore, $propertyBefore)
    {
        $report->add($this->context, new PropertyMoved($this->context, $fileBefore, $classBefore, $propertyBefore));
    }
}
