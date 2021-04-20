<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Analyzer;

use Magento\SemanticVersionChecker\ClassHierarchy\Entity;
use Magento\SemanticVersionChecker\Comparator\Visibility;
use Magento\SemanticVersionChecker\Operation\PropertyMoved;
use Magento\SemanticVersionChecker\Operation\PropertyOverwriteAdded;
use Magento\SemanticVersionChecker\Operation\Visibility\PropertyDecreased as VisibilityPropertyDecreased;
use Magento\SemanticVersionChecker\Operation\Visibility\PropertyIncreased as VisibilityPropertyIncreased;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PHPSemVerChecker\Operation\PropertyAdded;
use PHPSemVerChecker\Operation\PropertyRemoved;
use PHPSemVerChecker\Registry\Registry;
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
        return $property->props[0]->name->toString();
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
        if ($this->dependencyGraph !== null) {
            $class = $this->dependencyGraph->findEntityByName((string)$classAfter->namespacedName);
            if ($class !== null) {
                $propertyOverwritten = $this->searchPropertyExistsRecursive($class, $propertyAfter->props[0]->name->toString());
                if ($propertyOverwritten) {
                    $report->add(
                        $this->context,
                        new PropertyOverwriteAdded($this->context, $fileAfter, $classAfter, $propertyAfter)
                    );

                    return;
                }
            }
        }

        $report->add($this->context, new PropertyAdded($this->context, $fileAfter, $classAfter, $propertyAfter));
    }

    /**
     * Check if there is such property in class inheritance chain.
     *
     * @param Entity $class
     * @param string $propertyName
     * @return boolean
     */
    private function searchPropertyExistsRecursive($class, $propertyName)
    {
        /** @var Entity $entity */
        foreach ($class->getExtends() as $entity) {
            $properties = $entity->getPropertyList();
            // checks if the property is already exiting in parent class
            if (isset($properties[$propertyName])) {
                return true;
            }

            $result = $this->searchPropertyExistsRecursive($entity, $propertyName);
            if ($result) {
                return true;
            }
        }

        return false;
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

    /**
     * Find changes to nodes that exist in both before and after states and add them to the report
     *
     * @param Report $report
     * @param Node|Registry $contextBefore
     * @param Node|Registry $contextAfter
     * @param string[] $toVerify
     * @return void
     */
    protected function reportChanged($report, $contextBefore, $contextAfter, $propertyToVerify)
    {
        /** @var Property[] $beforeNameMap */
        $beforeNameMap = $this->getNodeNameMap($contextBefore);
        /** @var Property[] $afterNameMap */
        $afterNameMap = $this->getNodeNameMap($contextAfter);
        foreach ($propertyToVerify as $property) {
            /** @var \PhpParser\Node\Stmt\Property $propertyBefore */
            $propertyBefore = $beforeNameMap[$property];
            /** @var \PhpParser\Node\Stmt\Property $propertyAfter */
            $propertyAfter = $afterNameMap[$property];

            if ($propertyBefore !== $propertyAfter) {
                // Visibility
                $visibilityChanged = Visibility::analyze($propertyBefore, $propertyAfter);
                if ($visibilityChanged && $visibilityChanged > 0) {
                    $data = new VisibilityPropertyDecreased(
                        $this->context,
                        $this->fileBefore,
                        $contextBefore,
                        $propertyBefore,
                        $this->fileAfter,
                        $contextAfter,
                        $propertyAfter
                    );
                    $report->add($this->context, $data);
                }
                if ($visibilityChanged && $visibilityChanged < 0) {
                    $data = new VisibilityPropertyIncreased(
                        $this->context,
                        $this->fileBefore,
                        $contextBefore,
                        $propertyBefore,
                        $this->fileAfter,
                        $contextAfter,
                        $propertyAfter
                    );
                    $report->add($this->context, $data);
                }
            }
        }
    }
}
