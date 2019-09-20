<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Analyzer;

use Magento\SemanticVersionChecker\Comparator\Signature;
use Magento\SemanticVersionChecker\Operation\ClassConstructorLastParameterRemoved;
use Magento\SemanticVersionChecker\Operation\ClassConstructorObjectParameterAdded;
use Magento\SemanticVersionChecker\Operation\ClassConstructorOptionalParameterAdded;
use Magento\SemanticVersionChecker\Operation\ClassMethodLastParameterRemoved;
use Magento\SemanticVersionChecker\Operation\ClassMethodMoved;
use Magento\SemanticVersionChecker\Operation\ClassMethodOptionalParameterAdded;
use Magento\SemanticVersionChecker\Operation\ClassMethodParameterTypingChanged;
use Magento\SemanticVersionChecker\Operation\ExtendableClassConstructorOptionalParameterAdded;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PHPSemVerChecker\Comparator\Implementation;
use PHPSemVerChecker\Operation\ClassMethodAdded;
use PHPSemVerChecker\Operation\ClassMethodRemoved;
use PHPSemVerChecker\Operation\ClassMethodOperationUnary;
use PHPSemVerChecker\Operation\ClassMethodParameterAdded;
use PHPSemVerChecker\Operation\ClassMethodParameterRemoved;
use PHPSemVerChecker\Operation\ClassMethodParameterNameChanged;
use PHPSemVerChecker\Operation\ClassMethodImplementationChanged;
use PHPSemVerChecker\Report\Report;

/**
 * Class method analyzer.
 * Performs comparison of changed methods and creates reports such as:
 * - class method moved to parent
 * - class method removed
 * - class method added
 * - class method parameters changed
 * - class method implementation changed
 */
class ClassMethodAnalyzer extends AbstractCodeAnalyzer
{
    /**
     * List of API classes which constructors should be changed only in MAJOR releases as
     * these classes designed for the future extend.
     *
     * This method has a list of hardcoded classes because `Analyzer`s class parents do not
     * allow to provide any additional dependencies.
     */
    private $extendableApiClassList = [
        'Magento\Framework\Model\AbstractExtensibleModel',
        'Magento\Framework\Api\AbstractExtensibleObject',
        'Magento\Framework\Api\AbstractSimpleObject',
        'Magento\Framework\Model\AbstractModel',
        'Magento\Framework\Model\ResourceModel\AbstractResource',
        'Magento\Framework\App\Action\Action',
        'Magento\Backend\App\Action',
        'Magento\Backend\App\AbstractAction',
        'Magento\Framework\App\Action\AbstractAction',
        'Magento\Framework\View\Element\AbstractBlock',
        'Magento\Framework\View\Element\Template',
        'Magento\Framework\Data\Collection'
    ];

    /**
     * Get the name of a ClassMethod node
     *
     * @param ClassMethod $method
     * @return string
     */
    protected function getNodeName($method)
    {
        return $method->name;
    }

    /**
     * Use nodes of the ClassMethod type for this analyzer
     *
     * @return string
     */
    protected function getNodeClass()
    {
        return ClassMethod::class;
    }

    /**
     * Create and report a ClassMethodAdded operation
     *
     * @param Report $report
     * @param string $fileAfter
     * @param ClassLike $classAfter
     * @param ClassMethod $methodAfter
     * @return void
     */
    protected function reportAddedNode($report, $fileAfter, $classAfter, $methodAfter)
    {
        $report->add($this->context, new ClassMethodAdded($this->context, $fileAfter, $classAfter, $methodAfter));
    }

    /**
     * Create and report a ClassMethodRemoved operation
     *
     * @param Report $report
     * @param string $fileBefore
     * @param ClassLike $classBefore
     * @param ClassMethod $methodBefore
     * @return void
     */
    protected function reportRemovedNode($report, $fileBefore, $classBefore, $methodBefore)
    {
        $report->add($this->context, new ClassMethodRemoved($this->context, $fileBefore, $classBefore, $methodBefore));
    }

    /**
     * Create and report a ClassMethodMoved operation
     *
     * @param Report $report
     * @param string $fileBefore
     * @param ClassLike $classBefore
     * @param ClassMethod $methodBefore
     * @return void
     */
    protected function reportMovedNode($report, $fileBefore, $classBefore, $methodBefore)
    {
        $report->add($this->context, new ClassMethodMoved($this->context, $fileBefore, $classBefore, $methodBefore));
    }

    /**
     * Find changes to methods that exist in both before and after states and add them to the report
     *
     * @param Report $report
     * @param ClassLike $contextBefore
     * @param ClassLike $contextAfter
     * @param string[] $methodsToVerify
     * @return void
     */
    protected function reportChanged($report, $contextBefore, $contextAfter, $methodsToVerify)
    {
        /** @var ClassMethod[] $beforeNameMap */
        $beforeNameMap = $this->getNodeNameMap($contextBefore);
        /** @var ClassMethod[] $afterNameMap */
        $afterNameMap = $this->getNodeNameMap($contextAfter);
        foreach ($methodsToVerify as $method) {
            /** @var \PhpParser\Node\Stmt\ClassMethod $methodBefore */
            $methodBefore = $beforeNameMap[$method];
            /** @var \PhpParser\Node\Stmt\ClassMethod $methodAfter */
            $methodAfter = $afterNameMap[$method];

            if ($methodBefore !== $methodAfter) {
                $paramsBefore = $methodBefore->params;
                $paramsAfter = $methodAfter->params;

                // Signature
                $signatureChanged = false;
                $signatureChanges = Signature::analyze($paramsBefore, $paramsAfter);

                $beforeCount = count($paramsBefore);
                $afterCount = count($paramsAfter);
                $minCount= min($beforeCount, $afterCount);

                if ($signatureChanges['parameter_typing_added']) {
                    $data = new \PHPSemVerChecker\Operation\ClassMethodParameterTypingAdded(
                        $this->context,
                        $this->fileAfter,
                        $contextAfter,
                        $methodAfter
                    );
                    $report->add($this->context, $data);
                    $signatureChanged = true;
                }
                if ($signatureChanges['parameter_typing_removed']) {
                    $data = new \PHPSemVerChecker\Operation\ClassMethodParameterTypingRemoved(
                        $this->context,
                        $this->fileAfter,
                        $contextAfter,
                        $methodAfter
                    );
                    $report->add($this->context, $data);
                    $signatureChanged = true;
                }
                if ($signatureChanges['parameter_typing_changed']) {
                    $data = new ClassMethodParameterTypingChanged(
                        $this->context,
                        $this->fileAfter,
                        $contextAfter,
                        $methodAfter
                    );
                    $report->add($this->context, $data);
                    $signatureChanged = true;
                }

                $sameVarNames = !$signatureChanges['parameter_renamed'];

                if (!$signatureChanged && $beforeCount > $afterCount) {
                    $remainingBefore = array_slice($paramsBefore, $minCount);
                    if ($sameVarNames) {
                        if (strtolower($methodBefore->name) === "__construct") {
                            $data = new ClassConstructorLastParameterRemoved(
                                $this->context,
                                $this->fileAfter,
                                $contextAfter,
                                $methodAfter
                            );
                        } else {
                            $data = new ClassMethodLastParameterRemoved(
                                $this->context,
                                $this->fileAfter,
                                $contextAfter,
                                $methodAfter
                            );
                        }
                        $report->add($this->context, $data);
                        $signatureChanged = true;
                    } elseif (!Signature::isOptionalParams($remainingBefore)) {
                        $data = new ClassMethodParameterRemoved(
                            $this->context,
                            $this->fileAfter,
                            $contextAfter,
                            $methodAfter
                        );
                        $report->add($this->context, $data);
                        $signatureChanged = true;
                    }
                }

                if (!$signatureChanged && $beforeCount < $afterCount) {
                    $remainingAfter = array_slice($paramsAfter, $minCount);
                    if (strtolower($methodBefore->name) === '__construct') {
                        $data = $this->analyzeRemainingConstructorParams($contextAfter, $methodAfter, $remainingAfter);
                    } else {
                        $data = $this->analyzeRemainingMethodParams($contextAfter, $methodAfter, $remainingAfter);
                    }
                    $report->add($this->context, $data);
                    $signatureChanged = true;
                }

                if (!$signatureChanged && !$sameVarNames) {
                    $data = new ClassMethodParameterNameChanged(
                        $this->context,
                        $this->fileBefore,
                        $contextBefore,
                        $methodBefore,
                        $this->fileAfter,
                        $contextAfter,
                        $methodAfter
                    );
                    $report->add($this->context, $data);
                }
                // Difference in source code
                $stmtsBefore = empty($methodBefore->stmts) ? [] : $methodBefore->stmts;
                $stmtsAfter = empty($methodAfter->stmts) ? [] : $methodAfter->stmts;
                if (!Implementation::isSame($stmtsBefore, $stmtsAfter)) {
                    $data = new ClassMethodImplementationChanged(
                        $this->context,
                        $this->fileBefore,
                        $contextBefore,
                        $methodBefore,
                        $this->fileAfter,
                        $contextAfter,
                        $methodAfter
                    );
                    $report->add($this->context, $data);
                }
            }
        }
    }

    /**
     * Checks changed constructor parameters.
     *
     * @param Stmt $contextAfter
     * @param ClassMethod $methodAfter
     * @param array $remainingAfter
     * @return ClassMethodOperationUnary
     */
    private function analyzeRemainingConstructorParams($contextAfter, $methodAfter, $remainingAfter)
    {
        if (Signature::isOptionalParams($remainingAfter)) {
            $namespace = implode('\\', $contextAfter->jsonSerialize()['namespacedName']->parts);
            if (in_array($namespace, $this->extendableApiClassList)) {
                $data = new ExtendableClassConstructorOptionalParameterAdded(
                    $this->context,
                    $this->fileAfter,
                    $contextAfter,
                    $methodAfter
                );
            } else {
                $data = new ClassConstructorOptionalParameterAdded(
                    $this->context,
                    $this->fileAfter,
                    $contextAfter,
                    $methodAfter
                );
            }
        } else if (Signature::isObjectParams($remainingAfter)) {
            $data = new ClassConstructorObjectParameterAdded(
                $this->context,
                $this->fileAfter,
                $contextAfter,
                $methodAfter
            );
        } else {
            $data = new ClassMethodParameterAdded(
                $this->context,
                $this->fileAfter,
                $contextAfter,
                $methodAfter
            );
        }

        return $data;
    }

    /**
     * Checks method changed parameters.
     *
     * @param Stmt $contextAfter
     * @param ClassMethod $methodAfter
     * @param array $remainingAfter
     * @return ClassMethodOperationUnary
     */
    private function analyzeRemainingMethodParams($contextAfter, $methodAfter, $remainingAfter)
    {
        if (Signature::isOptionalParams($remainingAfter)) {
            $data = new ClassMethodOptionalParameterAdded(
                $this->context,
                $this->fileAfter,
                $contextAfter,
                $methodAfter
            );
        } else {
            $data = new ClassMethodParameterAdded(
                $this->context,
                $this->fileAfter,
                $contextAfter,
                $methodAfter
            );
        }

        return $data;
    }
}
