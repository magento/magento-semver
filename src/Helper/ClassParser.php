<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SemanticVersionCheckr\Helper;

use PhpParser\Lexer\Emulative;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Parser\Php7 as Parser;

class ClassParser
{
    /**
     * File path to parsed class.
     *
     * @var string
     */
    private $filePath;

    /**
     * Parser instance.
     *
     * @var Parser
     */
    private $parser;

    /**
     * Array of Magento autoload path.
     *
     * @var array
     */
    private $autoloadPathes = [
        "Magento\\Framework\\" => "lib/internal/Magento/Framework/",
        "Magento\\Setup\\" => "setup/src/Magento/Setup/",
        "Magento\\" => "app/code/Magento/"
    ];

    /**
     * @param string $filePath
     */
    public function __construct($filePath)
    {
        $this->filePath = $filePath;
        $this->parser = new Parser(new Emulative());
    }

    /**
     * Returns instance of parsed parent class.
     *
     * @return ClassParser|null
     */
    public function getParentClass()
    {
        $parentClass = $this->getParentFullClassName();

        return $parentClass === null ? null : new ClassParser($this->retrieveFilePath($parentClass));
    }

    /**
     * Returns parent full class name.
     *
     * @return null|string
     */
    public function getParentFullClassName()
    {
        if (!file_exists($this->filePath)) {
            return null;
        }
        $extendedClass = null;

        $nodeTree = $this->getNamespaceNode();
        foreach ($nodeTree->stmts as $stmt) {
            if ($stmt instanceof Class_ && $stmt->extends !== null) {
                if (count($stmt->extends->parts) > 1) {
                    return implode("\\", $stmt->extends->parts);
                }
                $extendedClass = end($stmt->extends->parts);
            }
        }

        if ($extendedClass === null) {
            return null;
        }

        foreach ($nodeTree->stmts as $stmt) {
            if ($stmt instanceof Use_ && $stmt->uses[0]->alias === $extendedClass) {
                return implode("\\", $stmt->uses[0]->name->parts);
            }
        }

        return $nodeTree->name->toString() . "\\" . $extendedClass;
    }

    /**
     * Returns array of parsed implemented interfaces.
     *
     * @return ClassParser[]
     */
    public function getImplementedInterfaces()
    {
        $result = [];
        $implementedInterfaces = $this->getImplementedInterfacesNames();
        foreach ($implementedInterfaces as $implementedInterface) {
            $result[] = new ClassParser($this->retrieveFilePath($implementedInterface));
        }

        return $result;
    }

    /**
     * Returns method of current parsed class or interface.
     *
     * @return array
     */
    public function getMethods()
    {
        if (!file_exists($this->filePath)) {
            return [];
        }
        $result = [];

        $nodeTree = $this->getNamespaceNode();
        foreach ($nodeTree->stmts as $stmt) {
            if ($stmt instanceof Class_) {
                return $stmt->getMethods();
            }
        }

        return $result;
    }

    /**
     * Returns member nodes of a specified type from the current parsed class
     *
     * @param string $nodeClass
     * @return Node[]
     */
    public function getNodesOfType($nodeClass)
    {
        if (!file_exists($this->filePath)) {
            return [];
        }

        $nodeTree = $this->getNamespaceNode();
        foreach ($nodeTree->stmts as $stmt) {
            if ($stmt instanceof ClassLike) {
                return self::filterNodes($stmt, $nodeClass);
            }
        }

        return [];
    }

    /**
     * Filters the children of a given parent node by a specified type
     *
     * @param Node $obj
     * @param string $filterClass
     * @return Node[] array
     */
    public static function filterNodes($obj, $filterClass)
    {
        $result = [];
        if (property_exists($obj, 'stmts')) {
            foreach ($obj->stmts as $node) {
                if (is_a($node, $filterClass)) {
                    $result[] = $node;
                }
            }
        }

        return $result;
    }

    /**
     * Retrieves the ancestors of <var>$className</var> that is found in current file.
     *
     * Note that <var>$className</var> may refer to either an actual class, interface or trait defined in the file or
     * any class, interface or trait that is used in the file.
     *
     * Currently only actual parents of <var>$className</var> are returned, implemented interfaces and used traits are
     * ignored!
     *
     * @param string $className
     * @return array The ancestors of <var>$className</var> if it could be found, empty array otherwise
     */
    public function getAncestors(string $className): array
    {
        $ancestors               = [];
        $fullyQualifiedClassName = $this->getFullyQualifiedName($className);

        //bail out if className could not be resolved
        if (strlen($fullyQualifiedClassName) === 0) {
            return $ancestors;
        }

        $classParser     = new ClassParser($this->retrieveFilePath($fullyQualifiedClassName));
        $parentClassName = $classParser->getParentFullClassName();

        while ($parentClassName !== null) {
            $ancestors[]     = $parentClassName;
            $classParser     = $classParser->getParentClass();
            $parentClassName = $classParser->getParentFullClassName();
        }

        return $ancestors;
    }

    /**
     * Returns properties of current parsed class.
     *
     * @return array
     */
    public function getProperties()
    {
        return $this->getNodesOfType(Property::class);
    }

    /**
     * Returns constants of current parsed class or interface.
     *
     * @return array
     */
    public function getConstants()
    {
        return $this->getNodesOfType(ClassConst::class);
    }

    /**
     * Returns array of full names which implemented at current class.
     *
     * @return array
     */
    public function getImplementedInterfacesNames()
    {
        if (!file_exists($this->filePath)) {
            return [];
        }
        $result = [];
        $uses = [];
        $nodeTree = $this->getNamespaceNode();

        $namespace = $nodeTree->name->toString();
        foreach ($nodeTree->stmts as $stmt) {
            if ($stmt instanceof Use_) {
                $uses[$stmt->uses[0]->alias] = $stmt->uses[0];
            }
        }

        foreach ($nodeTree->stmts as $stmt) {
            if ($stmt instanceof Class_ && !empty($stmt->implements)) {
                foreach ($stmt->implements as $interfaceName) {
                    if ($interfaceName->isFullyQualified()) {
                        $result[] = $interfaceName->toString();
                    } elseif (isset($uses[$interfaceName->getLast()])) {
                        $result[] = $uses[$interfaceName->getLast()]->name->toString();
                    } else {
                        $result[] = $namespace . "\\" . $interfaceName->getLast();
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Returns the fully qualified name of <var>$alias</var> in current file.
     *
     * This is useful for resolving aliases that were found in e.g. PHP DocBlocks.
     *
     * @param string $alias
     * @return string Empty string if alias cannot be resolved, fully qualified name of alias otherwise
     */
    public function getFullyQualifiedName(string $alias): string
    {
        //bail out if file does not exist
        if (!file_exists($this->filePath)) {
            return '';
        }

        //bail out if alias is already fully qualified (i.e. native classes like \Exception)
        if (class_exists($alias, false)) {
            return $alias;
        }

        try {
            $nodeTree = $this->getNamespaceNode();

            foreach ($nodeTree->stmts as $stmt) {
                //is the class, interface, trait defined in the very same file?
                if ($stmt instanceof ClassLike
                    && $stmt->name === $alias
                ) {
                    return $nodeTree->name->toString() . '\\' . $stmt->name;
                }

                //is the class being imported?
                if ($stmt instanceof Use_) {
                    foreach ($stmt->uses as $useUseStmnt) {
                        $fullyQualifiedName = $useUseStmnt->name->toString();

                        if ($useUseStmnt->alias === $alias || $fullyQualifiedName === $alias) {
                            return $fullyQualifiedName;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            //NOP We simply return an empty string
        }

        //we could not find the alias, thus we return an empty string
        return '';

    }

    /**
     * Retrieves file path from full class or interface name.
     *
     * @param string $namespace
     * @return string
     */
    private function retrieveFilePath($namespace)
    {
        // Concession to SVC unit testing; test classes do not exist in the normal source paths
        $testDirPath = 'SemanticVersionChecker' . DIRECTORY_SEPARATOR . 'Test' . DIRECTORY_SEPARATOR;
        if (strpos($this->filePath, $testDirPath) !== false) {
            $testSourceDir = substr($this->filePath, 0, strrpos($this->filePath, DIRECTORY_SEPARATOR));
            $fileName = substr($namespace, strrpos($namespace, '\\') ?: 0) . '.php';
            return str_replace('\\', DIRECTORY_SEPARATOR, $testSourceDir . $fileName);
        }

        $result = $this->retrieveSourcePath();
        foreach ($this->autoloadPathes as $namespaceArea => $path) {
            if (strpos($namespace, $namespaceArea) !== false) {
                $namespace = str_replace($namespaceArea, '', $namespace);
                $result .= $path;
                break;
            }
        }

        return $result . str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . '.php';
    }

    /**
     * Retrieves Magento source path based on path to parsed file.
     *
     * @return string
     */
    private function retrieveSourcePath()
    {
        $result = '';
        foreach ($this->autoloadPathes as $path) {
            $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
            $result = strpos($this->filePath, $path) !== false ? explode($path, $this->filePath)[0] : $result;
        }

        return $result;
    }

    /**
     * Get the main namespace node from class/interface file
     *
     * @return Namespace_
     * @throws \Exception
     */
    private function getNamespaceNode()
    {
        $allNodes = $this->parser->parse(file_get_contents($this->filePath));
        foreach ($allNodes as $node) {
            if ($node instanceof Namespace_) {
                return $node;
            }
        }
        throw new \Exception('No namespace definition found in ' . $this->filePath);
    }
}
