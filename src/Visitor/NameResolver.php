<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Visitor;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeAbstract;
use PhpParser\NodeVisitor\NameResolver as ParserNameResolver;
use PhpParser\BuilderHelpers;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;

/**
 * Extended Name Resolver that parse and resolve also docblock params and return type hinting.
 */
class NameResolver extends ParserNameResolver
{
    /**
     * Internal types that should not be resolved for docblock
     *
     * @var string[]
     */
    private $internalTypes = [
        'string',
        'integer',
        'float',
        'double',
        'boolean',
        'bool',
        'array',
        'object',
        'null',
        'resource',
        '$this',
    ];

    /**
     * @inheritDoc
     */
    public function enterNode(Node $node)
    {
        $return = parent::enterNode($node);

        if ($node instanceof ClassMethod) {
            $this->resolveDocBlockParamTypes($node);
        }

        return $return;
    }

    /**
     * @param ClassMethod $node
     * @return void
     */
    private function resolveDocBlockParamTypes(ClassMethod $node)
    {
        /** @var PhpDocNode $docNode */
        $docNode = $this->getParsedDocNode($node);
        if ($docNode) {
            $result = [];
            /** @var ParamTagValueNode[] $paramTags */
            $paramTags = $docNode->getParamTagValues();
            /** @var ParamTagValueNode $paramTag */
            foreach ($paramTags as $paramTag) {
                $paramNode = [
                    'name' => $paramTag->parameterName ?? '',
                    'type' => $this->parseType($paramTag->type),
                ];
                $result['params'][] = $paramNode;
            }

            /** @var ReturnTagValueNode[] $returnTags */
            $returnTags = $docNode->getReturnTagValues();
            /** @var ReturnTagValueNode $returnTag */
            $returnTag = array_shift($returnTags);
            if ($returnTag) {
                $result['return'] = $this->parseType($returnTag->type);
            }
            $node->setAttribute('docCommentParsed', $result);
        }
    }

    /**
     * Parse param or return type into array of resolved types
     *
     * @param TypeNode $type
     * @return array
     */
    private function parseType($type)
    {
        $result = [];
        if ($type instanceof UnionTypeNode) {
            foreach ($type->types as $typeNode) {
                $result[] = $this->normalizeAndResolve($typeNode);
            }
        } else {
            $result[] = $this->normalizeAndResolve($type);
        }

        uasort(
            $result,
            function ($elementOne, $elementTwo) {
                return ((string)$elementOne < (string)$elementTwo) ? -1 : 1;
            }
        );

        return $result;
    }

    /**
     * @param TypeNode $type
     * @return NodeAbstract
     */
    private function normalizeAndResolve($type)
    {
        $normalizedType = BuilderHelpers::normalizeType((string)$type);

        if (in_array(strtolower((string)$type), $this->internalTypes)) {
            $resolvedType = $normalizedType;
        } else {
            $resolvedType = $this->resolveType($normalizedType);
        }

        return $resolvedType;
    }

    /**
     * Resolve type from Relative to FQCN
     *
     * @param $node
     * @return NodeAbstract
     */
    private function resolveType($node)
    {
        if ($node instanceof Name) {
            return $this->resolveClassName($node);
        }
        if ($node instanceof Node\NullableType) {
            $node->type = $this->resolveType($node->type);
            return $node;
        }
        if ($node instanceof Node\UnionType) {
            foreach ($node->types as &$type) {
                $type = $this->resolveType($type);
            }
            return $node;
        }
        return $node;
    }

    /**
     * Analyses the Method doc block and returns parsed node
     *
     * @param ClassMethod $method
     * @return PhpDocNode|null
     */
    private function getParsedDocNode(ClassMethod $method)
    {
        $docComment = $method->getDocComment();
        if ($docComment !== null) {
            $lexer = new Lexer();
            $typeParser = new TypeParser();
            $constExprParser = new ConstExprParser();
            $phpDocParser = new PhpDocParser($typeParser, $constExprParser);
            $tokens = $lexer->tokenize((string)$docComment);
            $tokenIterator = new TokenIterator($tokens);
            $phpDocNode = $phpDocParser->parse($tokenIterator);

            return $phpDocNode;
        }

        return null;
    }
}
