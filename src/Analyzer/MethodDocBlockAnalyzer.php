<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Analyzer;

use Magento\SemanticVersionChecker\Operation\DocblockAnnotations\ClassMethodParameterTypeMovedFromDocToInline;
use Magento\SemanticVersionChecker\Operation\DocblockAnnotations\ClassMethodParameterTypeMovedFromInlineToDoc;
use Magento\SemanticVersionChecker\Operation\DocblockAnnotations\ClassMethodReturnTypeMovedFromDocToInline;
use Magento\SemanticVersionChecker\Operation\DocblockAnnotations\ClassMethodReturnTypeMovedFromInlineToDoc;
use Magento\SemanticVersionChecker\Operation\DocblockAnnotations\ClassMethodVariableTypeMovedFromDocToInline;
use Magento\SemanticVersionChecker\Operation\DocblockAnnotations\ClassMethodVariableTypeMovedFromInlineToDoc;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Param;
use PHPSemVerChecker\Operation\ClassMethodOperationUnary;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;

/**
 * Method doc block analyzer.
 * Performs comparison of changed method doc blocks and creates reports such as:
 * - method param typehint moved from doc block to in-line
 * - method param typehint moved from in-line to doc block
 * - method return typehint moved from doc block to in-line
 * - method return typehint moved from in-line to doc block
 *
 * TODO: this class should be rewritten using new possibility added by
 * Magento\SemanticVersionChecker\Visitor\NameResolver
 * Now all information (and resolved typed) about DocBlock params and return type exists in
 * method node 'docCommentParsed' attribute
 */
class MethodDocBlockAnalyzer
{
    public const DOC_RETURN_TAG = '@return';
    public const DOC_PARAM_TAG = '@param';
    public const DOC_VAR_TAG = '@var';

    /**
     * Analyses the Method doc block and returns the return type declaration.
     *
     * @param ClassMethod $method
     * @param string $tagname
     *
     * @return array
     */
    public function getMethodDocDeclarationByTag(ClassMethod $method, string $tagname): array
    {
        $formattedTags = [];

        if ($method->getDocComment() !== null) {
            $lexer = new Lexer();
            $typeParser = new TypeParser();
            $constExprParser = new ConstExprParser();
            $phpDocParser = new PhpDocParser($typeParser, $constExprParser);

            $tokens = $lexer->tokenize((string)$method->getDocComment());
            $tokenIterator = new TokenIterator($tokens);
            $phpDocNode = $phpDocParser->parse($tokenIterator);
            $tags = $phpDocNode->getTagsByName($tagname);

            if ($tagname === self::DOC_RETURN_TAG) {
                /** @var PhpDocTagNode $tag */
                $tag = array_shift($tags);
                $formattedTags[0] = isset($tag) ? (string)$tag->value : '';
            } elseif (count($tags)) {
                /** @var PhpDocTagNode $tag */
                foreach ($tags as $tag) {
                    $tagName = $tag->value->parameterName ?? '';
                    if (empty($tagName)) {
                        $tagName = $tag->value->value ?? '';
                    }
                    if (empty($tagName)) {
                        $tagName = $tag->value->variableName ?? '';
                    }
                    $tagType = $tag->value->type->name ?? '';
                    if (!empty($tagType)) {
                        $formattedTags[$tagName] = $tagType;
                    }
                }
            }
        }

        return count($formattedTags) ? $formattedTags : [''];
    }

    /**
     * Analyzes the given methods and returns the specific operation
     * for movement between doc block and in-line declaration.
     *
     * @param ClassMethod $methodBefore
     * @param ClassMethod $methodAfter
     * @param string $context
     * @param string $fileAfter
     * @param ClassLike $contextAfter
     *
     * @return null|ClassMethodOperationUnary
     */
    public function analyzeTypeHintMovementsBetweenDocAndMethod(
        ClassMethod $methodBefore,
        ClassMethod $methodAfter,
        string $context,
        string $fileAfter,
        ClassLike $contextAfter
    ): ?ClassMethodOperationUnary {
        //check parameters
        $inlineParamTypesBefore = $this->getParamTypes($methodBefore->getParams());
        $inlineParamTypesAfter = $this->getParamTypes($methodAfter->getParams());
        $inlineParamTypesAdded = array_diff($inlineParamTypesAfter, $inlineParamTypesBefore) ?? [''];
        $inlineParamTypesRemoved = array_diff($inlineParamTypesBefore, $inlineParamTypesAfter) ?? [''];

        $docParamTypesBefore = $this->getMethodDocDeclarationByTag($methodBefore, self::DOC_PARAM_TAG) ?? [''];
        $docParamTypesAfter = $this->getMethodDocDeclarationByTag($methodAfter, self::DOC_PARAM_TAG) ?? [''];
        $docParamTypesAdded = array_diff($docParamTypesAfter, $docParamTypesBefore) ?? [''];
        $docParamTypesRemoved = array_diff($docParamTypesBefore, $docParamTypesAfter) ?? [''];

        //check return type
        $inlineReturnTypeBefore[] = $this->getTypeName($methodBefore->returnType);
        $inlineReturnTypeAfter[] = $this->getTypeName($methodAfter->returnType);
        $docReturnTypeBefore = $this->getMethodDocDeclarationByTag($methodBefore, self::DOC_RETURN_TAG) ?? [''];
        $docReturnTypeAfter = $this->getMethodDocDeclarationByTag($methodAfter, self::DOC_RETURN_TAG) ?? [''];
        $returnTypeMovedFromInlineToDoc = false;
        $returnTypeMovedFromDocToInline = false;
        if ($inlineReturnTypeBefore  !== $inlineReturnTypeAfter && $docReturnTypeBefore !== $docReturnTypeAfter) {
            $returnTypeMovedFromInlineToDoc = $inlineReturnTypeBefore[0] !== ''
                && $inlineReturnTypeBefore === $docReturnTypeAfter;
            $returnTypeMovedFromDocToInline = $inlineReturnTypeAfter[0] !== ''
                && $inlineReturnTypeAfter === $docReturnTypeBefore;
        }

        //check variables
        $docVarTypesBefore = $this->getMethodDocDeclarationByTag($methodBefore, self::DOC_VAR_TAG) ?? [''];
        $docVarTypesAfter = $this->getMethodDocDeclarationByTag($methodAfter, self::DOC_VAR_TAG) ?? [''];
        $docVarTypesAdded = array_diff($docVarTypesAfter, $docVarTypesBefore) ?? [''];
        $docVarTypesRemoved = array_diff($docVarTypesBefore, $docVarTypesAfter) ?? [''];
        switch (true) {
            case count($docParamTypesRemoved) && $inlineParamTypesAdded == $docParamTypesRemoved:
                return new ClassMethodParameterTypeMovedFromDocToInline(
                    $context,
                    $fileAfter,
                    $contextAfter,
                    $methodAfter
                );
            case count($docParamTypesAdded) && $inlineParamTypesRemoved == $docParamTypesAdded:
                return new ClassMethodParameterTypeMovedFromInlineToDoc(
                    $context,
                    $fileAfter,
                    $contextAfter,
                    $methodAfter
                );
            case $returnTypeMovedFromDocToInline:
                return new ClassMethodReturnTypeMovedFromDocToInline(
                    $context,
                    $fileAfter,
                    $contextAfter,
                    $methodAfter
                );
            case $returnTypeMovedFromInlineToDoc:
                return new ClassMethodReturnTypeMovedFromInlineToDoc(
                    $context,
                    $fileAfter,
                    $contextAfter,
                    $methodAfter
                );
            case count($docVarTypesRemoved) && $inlineParamTypesAdded == $docVarTypesRemoved:
                return new ClassMethodVariableTypeMovedFromDocToInline(
                    $context,
                    $fileAfter,
                    $contextAfter,
                    $methodAfter
                );
            case count($docVarTypesAdded) && $inlineParamTypesRemoved == $docVarTypesAdded:
                return new ClassMethodVariableTypeMovedFromInlineToDoc(
                    $context,
                    $fileAfter,
                    $contextAfter,
                    $methodAfter
                );
            default:
                return null;
        }
    }

    /**
     * Gives back an array of in-line param types by name.
     *
     * @param Param[] $params
     *
     * @return array
     */
    private function getParamTypes(array $params): array
    {
        $formattedParams = [];
        /** @var Param $param */
        foreach ($params as $param) {
            $paramType = $this->getTypeName($param->type);
            if (!empty($paramType)) {
                $formattedParams['$' . $param->var->name] = $paramType;
            }
        }

        return $formattedParams ?? [''];
    }

    /**
     * Resolve given type to name
     *
     * @param FullyQualified|Identifier|null $type
     *
     * @return string
     */
    private function getTypeName($type)
    {
        $typeClass = (is_null($type)) ? '' : get_class($type);
        switch ($typeClass) {
            case FullyQualified::class:
                $returnType = $type->getLast();
                break;
            case Identifier::class:
                $returnType = $type->toString();
                break;
            default:
                $returnType = '';
        }
        return $returnType;
    }
}
