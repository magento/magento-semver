<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Analyzer;

use Magento\SemanticVersionChecker\ClassHierarchy\Entity;
use Magento\SemanticVersionChecker\Comparator\Signature;
use Magento\SemanticVersionChecker\Comparator\Visibility;
use Magento\SemanticVersionChecker\Operation\ClassConstructorLastParameterRemoved;
use Magento\SemanticVersionChecker\Operation\ClassConstructorObjectParameterAdded;
use Magento\SemanticVersionChecker\Operation\ClassConstructorOptionalParameterAdded;
use Magento\SemanticVersionChecker\Operation\ClassMethodLastParameterRemoved;
use Magento\SemanticVersionChecker\Operation\ClassMethodMoved;
use Magento\SemanticVersionChecker\Operation\ClassMethodOptionalParameterAdded;
use Magento\SemanticVersionChecker\Operation\ClassMethodOverwriteAdded;
use Magento\SemanticVersionChecker\Operation\ClassMethodParameterTypingChanged;
use Magento\SemanticVersionChecker\Operation\ClassMethodReturnTypingChanged;
use Magento\SemanticVersionChecker\Operation\ExtendableClassConstructorOptionalParameterAdded;
use Magento\SemanticVersionChecker\Operation\Visibility\MethodDecreased as VisibilityMethodDecreased;
use Magento\SemanticVersionChecker\Operation\Visibility\MethodIncreased as VisibilityMethodIncreased;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\UnionType;
use PHPSemVerChecker\Comparator\Implementation;
use PHPSemVerChecker\Operation\ClassMethodAdded;
use PHPSemVerChecker\Operation\ClassMethodImplementationChanged;
use PHPSemVerChecker\Operation\ClassMethodOperationUnary;
use PHPSemVerChecker\Operation\ClassMethodParameterAdded;
use PHPSemVerChecker\Operation\ClassMethodParameterNameChanged;
use PHPSemVerChecker\Operation\ClassMethodParameterRemoved;
use PHPSemVerChecker\Operation\ClassMethodParameterTypingAdded;
use PHPSemVerChecker\Operation\ClassMethodParameterTypingRemoved;
use PHPSemVerChecker\Operation\ClassMethodRemoved;
use PHPSemVerChecker\Report\Report;

/**
 * Class method analyzer.
 * Performs comparison of changed methods and creates reports such as:
 * - class method moved to parent
 * - class method removed
 * - class method added
 * - class method parameters changed
 * - class method return type changed
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
        'Magento\Framework\Data\Collection',
    ];

    /** @var MethodDocBlockAnalyzer $methodDocBlockAnalyzer */
    private $methodDocBlockAnalyzer;

    /**
     * Get the name of a ClassMethod node
     *
     * @param ClassMethod $method
     * @return string
     */
    protected function getNodeName($method)
    {
        return $method->name->toString();
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
        if ($this->dependencyGraph === null) {
            $report->add($this->context, new ClassMethodAdded($this->context, $fileAfter, $classAfter, $methodAfter));
            return;
        }

        $class = $this->dependencyGraph->findEntityByName((string) $classAfter->namespacedName);

        if ($class !== null) {
            $methodOverwritten = $this->searchMethodExistsRecursive($class, $methodAfter->name->toString());
            if ($methodOverwritten) {
                $report->add(
                    $this->context,
                    new ClassMethodOverwriteAdded($this->context, $fileAfter, $classAfter, $methodAfter)
                );

                return;
            }
        }

        $report->add($this->context, new ClassMethodAdded($this->context, $fileAfter, $classAfter, $methodAfter));
    }

    /**
     * Check if there is such method in class inheritance chain.
     *
     * @param Entity $class
     * @param string $methodName
     * @return boolean
     */
    private function searchMethodExistsRecursive($class, $methodName)
    {
        /** @var Entity $entity */
        foreach ($class->getExtends() as $entity) {
            $methods = $entity->getMethodList();
            // checks if the method is already exiting in parent class
            if (isset($methods[$methodName])) {
                return true;
            }

            $result = $this->searchMethodExistsRecursive($entity, $methodName);
            if ($result) {
                return true;
            }
        }

        return false;
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
            /** @var ClassMethod $methodBefore */
            $methodBefore = $beforeNameMap[$method];
            /** @var ClassMethod $methodAfter */
            $methodAfter = $afterNameMap[$method];

            if ($methodBefore !== $methodAfter) {
                $paramsBefore = $methodBefore->params;
                $paramsAfter = $methodAfter->params;

                // Signature
                $signatureChanged = false;
                $signatureChanges = Signature::analyze($paramsBefore, $paramsAfter);

                $beforeCount = count($paramsBefore);
                $afterCount = count($paramsAfter);
                $minCount = min($beforeCount, $afterCount);
                $this->methodDocBlockAnalyzer = new MethodDocBlockAnalyzer();

                $typeData = $this->methodDocBlockAnalyzer->analyzeTypeHintMovementsBetweenDocAndMethod(
                    $methodBefore,
                    $methodAfter,
                    $this->context,
                    $this->fileAfter,
                    $contextAfter
                );
                if (!empty($typeData)) {
                    $report->add($this->context, $typeData);
                    $signatureChanged = true;
                } elseif ($this->isReturnTypeChanged($methodBefore, $methodAfter) === true) {
                    $data = new ClassMethodReturnTypingChanged(
                        $this->context,
                        $this->fileAfter,
                        $contextAfter,
                        $methodAfter
                    );
                    $report->add($this->context, $data);
                } elseif ($signatureChanges['parameter_typing_added']) {
                    $data = new ClassMethodParameterTypingAdded(
                        $this->context,
                        $this->fileAfter,
                        $contextAfter,
                        $methodAfter
                    );
                    $report->add($this->context, $data);
                    $signatureChanged = true;
                } elseif ($signatureChanges['parameter_typing_removed']) {
                    $data = new ClassMethodParameterTypingRemoved(
                        $this->context,
                        $this->fileAfter,
                        $contextAfter,
                        $methodAfter
                    );
                    $report->add($this->context, $data);
                    $signatureChanged = true;
                } elseif ($signatureChanges['parameter_typing_changed']) {
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
                        if (strtolower($methodBefore->name->toString()) === "__construct") {
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
                    if (strtolower($methodBefore->name->toString()) === '__construct') {
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

                // Visibility
                $visibilityChanged = Visibility::analyze($methodBefore, $methodAfter);
                if ($visibilityChanged && $visibilityChanged > 0) {
                    $data = new VisibilityMethodDecreased(
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
                if ($visibilityChanged && $visibilityChanged < 0) {
                    $data = new VisibilityMethodIncreased(
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
     * Checks if return type declaration or annotation was changed
     *
     * @param ClassMethod $methodBefore
     * @param ClassMethod $methodAfter
     *
     * @return bool
     */
    private function isReturnTypeChanged(ClassMethod $methodBefore, ClassMethod $methodAfter): bool
    {
        return $this->isDocBlockAnnotationReturnTypeChanged($methodBefore, $methodAfter)
            || $this->isDeclarationReturnTypeChanged($methodBefore, $methodAfter);
    }

    /**
     * @param ClassMethod $methodBefore
     * @param ClassMethod $methodAfter
     *
     * @return bool
     */
    private function isDocBlockAnnotationReturnTypeChanged(ClassMethod $methodBefore, ClassMethod $methodAfter)
    {
        $returnBefore = $this->getDocReturnDeclaration($methodBefore);
        $returnAfter  = $this->getDocReturnDeclaration($methodAfter);

        return $returnBefore !== $returnAfter;
    }

    /**
     * @param ClassMethod $methodBefore
     * @param ClassMethod $methodAfter
     *
     * @return bool
     */
    private function isDeclarationReturnTypeChanged(ClassMethod $methodBefore, ClassMethod $methodAfter)
    {
        if (!$this->isReturnsEqualByNullability($methodBefore, $methodAfter)) {
            return true;
        }

        $methodBeforeReturnType = $methodBefore->returnType;
        if ($methodBeforeReturnType instanceof NullableType) {
            $beforeMethodReturnType = (string)$methodBeforeReturnType->type;
        } elseif ($methodBeforeReturnType instanceof UnionType) {
            $beforeMethodReturnType = implode('&', $methodBeforeReturnType->types);
        } else {
            $beforeMethodReturnType = (string)$methodBeforeReturnType;
        }

        $methodAfterReturnType = $methodAfter->returnType;
        if ($methodAfterReturnType instanceof NullableType) {
            $afterMethodReturnType = (string)$methodAfterReturnType->type;
        } elseif ($methodAfterReturnType instanceof UnionType) {
            $afterMethodReturnType = implode('&', $methodAfterReturnType->types);
        } else {
            $afterMethodReturnType = (string)$methodAfterReturnType;
        }

        return $beforeMethodReturnType !== $afterMethodReturnType;
    }

    /**
     * checks if both return types has same nullable status
     *
     * @param ClassMethod $before
     * @param ClassMethod $after
     *
     * @return bool
     */
    private function isReturnsEqualByNullability(ClassMethod $before, ClassMethod $after): bool
    {
        return ($before instanceof NullableType) === ($after instanceof NullableType);
    }

    /**
     * Analyses the Method doc block and returns the return type declaration
     *
     * @param ClassMethod $method
     *
     * @return string
     */
    private function getDocReturnDeclaration(ClassMethod $method)
    {
        if (
            ($parsedComment = $method->getAttribute('docCommentParsed'))
            && isset($parsedComment['return'])
        ) {
            if ($parsedComment['return'][0] instanceof NullableType) {
                $result =  '?' . $parsedComment['return'][0]->type;
            } else {
                $result = implode('|', $parsedComment['return']);
            }

            return $result;
        } elseif ($this->dependencyGraph !== null) {
            /** @var Class_ $methodClass */
            $methodClass = $method->getAttribute('parent');
            if ($methodClass) {
                $ancestors = [];
                if (!empty($methodClass->extends)) {
                    $ancestors = $this->addAncestorsToArray($ancestors, $methodClass->extends);
                }
                if (!empty($methodClass->implements)) {
                    $ancestors = $this->addAncestorsToArray($ancestors, $methodClass->implements);
                }
                /** @var Name $ancestor */
                foreach ($ancestors as $ancestor) {
                    $ancestorClass = $this->dependencyGraph->findEntityByName($ancestor->toString());
                    if ($ancestorClass) {
                        foreach ($ancestorClass->getMethodList() as $methodItem) {
                            if ($method->name->toString() == $methodItem->name->toString()) {
                                $result = $this->getDocReturnDeclaration($methodItem);
                                if (!empty(trim($result))) {
                                    return $result;
                                }
                            }
                        }
                    }
                }
            }
        }

        return ' ';
    }

    /**
     * Add ancestors to array
     *
     * @param array $ancestors
     * @param array|Name $toAdd
     * @return array
     */
    private function addAncestorsToArray(array $ancestors, $toAdd)
    {
        if (!empty($toAdd)) {
            if (is_array($toAdd)) {
                $ancestors = array_merge($ancestors, $toAdd);
            } else {
                $ancestors[] = $toAdd;
            }
        }

        return $ancestors;
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
        } else {
            if (Signature::isObjectParams($remainingAfter)) {
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
