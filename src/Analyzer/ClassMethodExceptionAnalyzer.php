<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Analyzer;

use Magento\SemanticVersionChecker\Helper\ClassParser;
use Magento\SemanticVersionChecker\Operation\ExceptionSubclassed;
use Magento\SemanticVersionChecker\Operation\ExceptionSuperclassAdded;
use Magento\SemanticVersionChecker\Operation\ExceptionSuperclassed;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PHPSemVerChecker\Report\Report;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;

/**
 * Class method exception analyzer.
 * Performs comparison of changed methods and creates reports such as:
 * - class method's exception has been superclassed
 * - class method's exception has been subclassed
 * - a superclass of the existing exception has been added to class method
 */
class ClassMethodExceptionAnalyzer extends AbstractCodeAnalyzer
{

    /**
     * Caches the ancestors of classes.
     *
     * @var array<string,string[]>
     */
    private $classAncestors = [];

    /**
     * Get the name of a ClassMethod node
     *
     * @param ClassMethod $node
     * @return string
     */
    protected function getNodeName($node)
    {
        return $node->name->toString();
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
     * Do nothing since we do not analyze added methods
     *
     * @param Report $report
     * @param string $fileAfter
     * @param ClassLike $classAfter
     * @param ClassMethod $methodAfter
     * @return void
     */
    protected function reportAddedNode($report, $fileAfter, $classAfter, $methodAfter)
    {
        //NOP: This is not necessary for our context
    }

    /**
     * Do nothing since we do not analyze removed methods
     *
     * @param Report $report
     * @param string $fileBefore
     * @param ClassLike $classBefore
     * @param ClassMethod $methodBefore
     * @return void
     */
    protected function reportRemovedNode($report, $fileBefore, $classBefore, $methodBefore)
    {
        //NOP: This is not necessary for our context
    }

    /**
     * Do nothing since we do not analyze moved methods
     *
     * @param Report $report
     * @param string $fileBefore
     * @param ClassLike $classBefore
     * @param ClassMethod $methodBefore
     * @return void
     */
    protected function reportMovedNode($report, $fileBefore, $classBefore, $methodBefore)
    {
        //NOP: This is not necessary for our context
    }

    /**
     * Find changes to exceptions thrown by methods that exist in both before and after states and add them to the
     * report
     *
     * @param Report $report
     * @param ClassLike $contextBefore
     * @param ClassLike $contextAfter
     * @param string[] $methodsToVerify
     * @return void
     */
    protected function reportChanged($report, $contextBefore, $contextAfter, $methodsToVerify): void
    {
        /** @var ClassMethod[] $beforeNameMap */
        $beforeNameMap = $this->getNodeNameMap($contextBefore);
        /** @var ClassMethod[] $afterNameMap */
        $afterNameMap = $this->getNodeNameMap($contextAfter);

        foreach ($methodsToVerify as $method) {
            /** @var ClassMethod $methodBefore */
            $methodBefore = $beforeNameMap[$method];
            /** @var ClassMethod $methodAfter */
            $methodAfter    = $afterNameMap[$method];
            $exceptionsDiff = $this->getExceptionsDiff($methodBefore, $methodAfter);

            if ($this->hasSubclassedException($exceptionsDiff)) {
                $data = new ExceptionSubclassed(
                    $this->context,
                    $this->fileBefore,
                    $contextBefore,
                    $methodBefore,
                    $this->fileAfter,
                    $contextAfter,
                    $methodAfter
                );
                $report->add($this->context, $data);
            };

            if ($this->hasSuperclassedException($exceptionsDiff)) {
                $data = new ExceptionSuperclassed(
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

            if ($this->hasAddedSuperclassException($exceptionsDiff)) {
                $data = new ExceptionSuperclassAdded(
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

    /**
     * Analyzes the method doc block and returns all exceptions found in throws declarations.
     *
     * Note that <var>$file</var> is used to resolve and return the canonical class name of the exceptions in case they
     * were imported.
     *
     * @param ClassMethod $method
     * @param string $file The file in which <var>$method</var> is declared
     *
     * @return string[] An array of fully qualified exception class names
     */
    private function getExceptions(ClassMethod $method, string $file): array
    {
        $throws = [];

        if ($method->getDocComment() !== null) {
            $phpDocNode = $this->getParsedPhpDoc($method);
            $tags       = $phpDocNode->getTagsByName('@throws');

            foreach ($tags as $tag) {
                $exception = $this->resolveClass((string)$tag->value, $file);

                if (strlen($exception)) {
                    $throws[] = $exception;
                }
            }
        }

        return $throws;
    }

    /**
     * Returns an array containing all exceptions that were added and removed from a method.
     *
     * The returned array has the following keys:
     * <ul>
     *   <li><kbd>added</kbd>: The exceptions that have been added in <var>$methodAfter</var></li>
     *   <li><kbd>removed</kbd>: The exceptions that have been removed from <var>$methodBefore</var></li>
     *   <li><kbd>common</kbd>: The exceptions that are common to both versions</li>
     * </ul>
     *
     * @param ClassMethod $methodBefore
     * @param ClassMethod $methodAfter
     * @return array
     */
    private function getExceptionsDiff(ClassMethod $methodBefore, ClassMethod $methodAfter): array
    {
        $exceptionsBefore = $this->getExceptions($methodBefore, $this->fileBefore);
        $exceptionsAfter  = $this->getExceptions($methodAfter, $this->fileAfter);

        return [
            'added'   => array_diff($exceptionsAfter, $exceptionsBefore),
            'removed' => array_diff($exceptionsBefore, $exceptionsAfter),
            'common'  => array_intersect($exceptionsBefore, $exceptionsAfter),
        ];
    }

    /**
     * Returns a parsed php doc node that can be used to find specific annotations.
     *
     * @param ClassMethod $method
     * @return PhpDocNode
     */
    private function getParsedPhpDoc(ClassMethod $method): PhpDocNode
    {
        $lexer        = new Lexer();
        $phpDocParser = new PhpDocParser(
            new TypeParser(),
            new ConstExprParser()
        );

        $tokens        = $lexer->tokenize((string)$method->getDocComment());
        $tokenIterator = new TokenIterator($tokens);

        return $phpDocParser->parse($tokenIterator);
    }

    /**
     * Returns whether any exception of was subclassed.
     *
     * @param array $exceptionsDiff
     * @return bool
     */
    private function hasSubclassedException(array $exceptionsDiff): bool
    {
        foreach ($exceptionsDiff['added'] as $addedException) {
            foreach ($exceptionsDiff['removed'] as $removedException) {
                if ($this->isSubClassOf($addedException, $removedException)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Returns whether any exception was superclassed.
     *
     * @param array $exceptionsDiff
     * @return bool
     */
    private function hasSuperclassedException(array $exceptionsDiff): bool
    {
        foreach ($exceptionsDiff['added'] as $addedException) {
            //search for a child exception in previous state
            foreach ($exceptionsDiff['removed'] as $removedException) {
                if ($this->isSubClassOf($removedException, $addedException)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Returns whether a superclass exception was added to <var>$methodAfter</var> in comparison to
     * <var>$methodBefore</var>.
     *
     * @param array $exceptionsDiff
     * @return bool
     */
    private function hasAddedSuperclassException(array $exceptionsDiff): bool
    {
        foreach ($exceptionsDiff['added'] as $addedException) {
            //search for child exception in common state
            foreach ($exceptionsDiff['common'] as $commonException) {
                if ($this->isSubClassOf($commonException, $addedException)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Returns whether <var>$childClass</var> is a subclass of <var>$parentClass</var>.
     *
     * @param $childClass
     * @param $parentClass
     * @return bool
     */
    private function isSubClassOf($childClass, $parentClass): bool
    {
        $ancestors = $this->classAncestors[$childClass] ?? [];
        return in_array($parentClass, $ancestors);
    }

    /**
     * Attempts to resolve <var>$alias</var> that was found in <var>$file</var>.
     *
     * Resolving <var>$alias</var> performs two tasks:
     * 1. Resolving the <var>$alias</var> found in <var>$file</var> to its fully qualified name
     * 2. Determining its ancestors and storing them in {@link ClassMethodExceptionAnalyzer::$classAncestors}.
     *
     * @param string $alias
     * @param string $file
     * @return string The fully qualified name of the class if it could be found, empty string otherwise
     */
    private function resolveClass(string $alias, string $file): string
    {
        $classParser             = new ClassParser($file);
        $fullyQualifiedClassName = $classParser->getFullyQualifiedName($alias);

        if (strlen($fullyQualifiedClassName) > 0 && !isset($this->classAncestors[$fullyQualifiedClassName])) {
            $this->classAncestors[$fullyQualifiedClassName] = $classParser->getAncestors($fullyQualifiedClassName);
        }

        return $fullyQualifiedClassName;
    }
}
