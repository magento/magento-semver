<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Visitor;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeVisitor\NameResolver as ParserNameResolver;
use PhpParser\BuilderHelpers;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;

/**
 * Extended Name Resolver that parse and resolve also docblock hintings
 */
class NameResolver extends ParserNameResolver
{
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
            /** @var PhpDocTagNode[] $paramTags */
            $paramTags = $docNode->getTagsByName('@param');
            /** @var PhpDocTagNode $paramTag */
            foreach ($paramTags as $paramTag) {
                $paramNode = [
                    'name' => $paramTag->value->parameterName ?? '',
                    'type' => $this->parseType($paramTag->value->type),
                ];
                $result['params'][] = $paramNode;
            }

            /** @var PhpDocTagNode[] $returnTags */
            $returnTags = $docNode->getTagsByName('@return');
            /** @var PhpDocTagNode $returnTag */
            $returnTag = array_shift($returnTags);
            if ($returnTag) {
                $result['return'] = $this->parseType($returnTag->value->type);
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
                $normalizedType = BuilderHelpers::normalizeType((string)$typeNode);
                $resolvedType = $this->resolveType($normalizedType);
                $result[] = $resolvedType;
            }
        } else {
            $normalizedType = BuilderHelpers::normalizeType((string)$type);
            $resolvedType = $this->resolveType($normalizedType);
            $result[] = $resolvedType;
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
     * Resolve type from Relative to FQCN
     *
     * @param $node
     * @return Name|Node\NullableType|Node\UnionType
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
