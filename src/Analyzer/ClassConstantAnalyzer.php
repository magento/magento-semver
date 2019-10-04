<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tools\SemanticVersionChecker\Analyzer;

use Magento\Tools\SemanticVersionChecker\Comparator\Visibility;
use Magento\Tools\SemanticVersionChecker\Operation\ClassConstantAdded;
use Magento\Tools\SemanticVersionChecker\Operation\ClassConstantMoved;
use Magento\Tools\SemanticVersionChecker\Operation\ClassConstantRemoved;
use Magento\Tools\SemanticVersionChecker\Operation\Visibility\ConstantDecreased as VisibilityConstantDecreased;
use Magento\Tools\SemanticVersionChecker\Operation\Visibility\ConstantIncreased as VisibilityConstantIncreased;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PHPSemVerChecker\Operation\Visibility as VisibilityOperation;
use PHPSemVerChecker\Registry\Registry;
use PHPSemVerChecker\Report\Report;

/**
 * Class constant analyzer.
 * Performs comparison of changed constants and creates reports such as:
 * - class constants moved to parent
 * - class constants removed
 * - class constants added
 */
class ClassConstantAnalyzer extends AbstractCodeAnalyzer
{
    /**
     * Get the name of a ClassConst node
     *
     * @param ClassConst $constant
     * @return string
     */
    protected function getNodeName($constant)
    {
        return $constant->consts[0]->name;
    }

    /**
     * Use nodes of the Property type for this analyzer
     *
     * @return string
     */
    protected function getNodeClass()
    {
        return ClassConst::class;
    }

    /**
     * Create and report a ClassConstantAdded operation
     *
     * @param Report $report
     * @param string $fileAfter
     * @param ClassLike $classAfter
     * @param ClassConst $constantAfter
     * @return void
     */
    protected function reportAddedNode($report, $fileAfter, $classAfter, $constantAfter)
    {
        // check for false-positive trigger on the private const
        $visibility = VisibilityOperation::getForContext($constantAfter);
        if ($visibility === Class_::MODIFIER_PRIVATE) {
            return;
        }

        $report->add($this->context, new ClassConstantAdded($this->context, $fileAfter, $constantAfter, $classAfter));
    }

    /**
     * Create and report a ClassConstantRemoved operation
     *
     * @param Report $report
     * @param string $fileBefore
     * @param ClassLike $classBefore
     * @param ClassConst $constantBefore
     * @return void
     */
    protected function reportRemovedNode($report, $fileBefore, $classBefore, $constantBefore)
    {
        // check for false-positive trigger on the private const
        $visibility = VisibilityOperation::getForContext($constantBefore);
        if ($visibility === Class_::MODIFIER_PRIVATE) {
            return;
        }

        $report->add(
            $this->context,
            new ClassConstantRemoved($this->context, $fileBefore, $constantBefore, $classBefore)
        );
    }

    /**
     * Create and report a ClassConstantMoved operation
     *
     * @param Report $report
     * @param string $fileBefore
     * @param ClassLike $classBefore
     * @param ClassConst $constantBefore
     * @return void
     */
    protected function reportMovedNode($report, $fileBefore, $classBefore, $constantBefore)
    {
        $report->add(
            $this->context,
            new ClassConstantMoved($this->context, $fileBefore, $constantBefore, $classBefore)
        );
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
    protected function reportChanged($report, $contextBefore, $contextAfter, $constantToVerify)
    {
        /** @var Property[] $beforeNameMap */
        $beforeNameMap = $this->getNodeNameMap($contextBefore);
        /** @var Property[] $afterNameMap */
        $afterNameMap = $this->getNodeNameMap($contextAfter);
        foreach ($constantToVerify as $constant) {
            /** @var \PhpParser\Node\Stmt\Property $constantBefore */
            $constantBefore = $beforeNameMap[$constant];
            /** @var \PhpParser\Node\Stmt\Property $constantAfter */
            $constantAfter = $afterNameMap[$constant];

            if ($constantBefore !== $constantAfter) {
                // Visibility
                $visibilityChanged = Visibility::analyze($constantBefore, $constantAfter);
                if ($visibilityChanged && $visibilityChanged > 0) {
                    $data = new VisibilityConstantDecreased(
                        $this->context,
                        $this->fileBefore,
                        $contextBefore,
                        $constantBefore,
                        $this->fileAfter,
                        $contextAfter,
                        $constantAfter
                    );
                    $report->add($this->context, $data);
                }
                if ($visibilityChanged && $visibilityChanged < 0) {
                    $data = new VisibilityConstantIncreased(
                        $this->context,
                        $this->fileBefore,
                        $contextBefore,
                        $constantBefore,
                        $this->fileAfter,
                        $contextAfter,
                        $constantAfter
                    );
                    $report->add($this->context, $data);
                }
            }
        }
    }
}
