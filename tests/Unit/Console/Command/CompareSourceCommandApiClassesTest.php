<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit\Console\Command;

use Magento\SemanticVersionChecker\Test\Unit\Console\Command\CompareSourceCommandTest\AbstractTestCase;

/**
 * Test semantic version checker CLI command dealing with API classes.
 */
class CompareSourceCommandApiClassesTest extends AbstractTestCase
{
    /**
     * Test semantic version checker CLI command for classes that have the <kbd>api</kbd> annotation.
     *
     * @param string $pathToSourceCodeBefore
     * @param string $pathToSourceCodeAfter
     * @param string[] $expectedLogEntries
     * @param string $expectedOutput
     * @param string[] $unexpectedLogEntries
     * @return void
     * @throws \Exception
     * @dataProvider changesDataProvider
     */
    public function testExecute(
        $pathToSourceCodeBefore,
        $pathToSourceCodeAfter,
        $expectedLogEntries,
        $expectedOutput,
        $unexpectedLogEntries = []
    ) {
        $this->doTestExecute(
            $pathToSourceCodeBefore,
            $pathToSourceCodeAfter,
            $expectedLogEntries,
            $expectedOutput,
            $unexpectedLogEntries
        );
    }

    public function changesDataProvider()
    {
        $pathToFixtures = __DIR__ . '/CompareSourceCommandTest/_files/api-class';
        return [
            'api-class-new-class' => [
                $pathToFixtures . '/new-class/source-code-before',
                $pathToFixtures . '/new-class/source-code-after',
                [
                    'Class (MINOR)',
                    'Test\Vcs\TestClass | Class was added. | V014'
                ],
                'Minor change is detected.'
            ],
            'api-class-new-method' => [
                $pathToFixtures . '/new-method/source-code-before',
                $pathToFixtures . '/new-method/source-code-after',
                [
                    'Class (MINOR)',
                    'Test\Vcs\TestClass::testMethod | [public] Method has been added. | V015'
                ],
                'Minor change is detected.'
            ],
            'api-class-removed-class' => [
                $pathToFixtures . '/removed-class/source-code-before',
                $pathToFixtures . '/removed-class/source-code-after',
                [
                    'Class (MAJOR)',
                    'Test\Vcs\TestClass | Class was removed. | V005'
                ],
                'Major change is detected.'
            ],
            'api-class-removed-method' => [
                $pathToFixtures . '/removed-method/source-code-before',
                $pathToFixtures . '/removed-method/source-code-after',
                [
                    'Class (MAJOR)',
                    'Test\Vcs\TestClass::testMethod | [public] Method has been removed. | V006'
                ],
                'Major change is detected.'
            ],
            'api-class-new-required-method-parameter' => [
                $pathToFixtures . '/new-required-method-parameter/source-code-before',
                $pathToFixtures . '/new-required-method-parameter/source-code-after',
                [
                    'Class (MAJOR)',
                    'Test\Vcs\TestClass::testMethod | [public] Method parameter added. | V010'
                ],
                'Major change is detected.'
            ],
            'api-class-new-optional-method-parameter' => [
                $pathToFixtures . '/new-optional-method-parameter/source-code-before',
                $pathToFixtures . '/new-optional-method-parameter/source-code-after',
                [
                    'Class (MINOR)',
                    'Test\Vcs\TestClass::publicMethod    | [public] Added optional parameter(s).    | M102',
                    'Test\Vcs\TestClass::protectedMethod | [protected] Added optional parameter(s). | M102',
                    'Test\Vcs\TestClass::privateMethod   | [private] Added optional parameter(s).   | M102',
                    'PATCH'
                ],
                'Minor change is detected.'
            ],
            'api-class-removed-optional-non-last-method-parameter' => [
                $pathToFixtures . '/removed-optional-non-last-method-parameter/source-code-before',
                $pathToFixtures . '/removed-optional-non-last-method-parameter/source-code-after',
                [
                    'Class (MAJOR)',
                    'Test\Vcs\TestClass::testMethod | [public] Method parameter name changed. | V060'
                ],
                'Major change is detected.'
            ],
            'api-class-removed-required-method-parameter-followed-by-optional-one' => [
                $pathToFixtures . '/removed-required-method-parameter-followed-by-optional-one/source-code-before',
                $pathToFixtures . '/removed-required-method-parameter-followed-by-optional-one/source-code-after',
                [
                    'Class (MAJOR)',
                    'Test\Vcs\TestClass::testMethod | [public] Method parameter name changed. | V060'
                ],
                'Major change is detected.'
            ],
            'api-class-removed-required-non-last-method-parameter' => [
                $pathToFixtures . '/removed-required-non-last-method-parameter/source-code-before',
                $pathToFixtures . '/removed-required-non-last-method-parameter/source-code-after',
                [
                    'Class (MAJOR)',
                    'Test\Vcs\TestClass::testMethod | [public] Method parameter removed. | V082'
                ],
                'Major change is detected.'
            ],
            'api-class-new-required-constructor-parameter' => [
                $pathToFixtures . '/new-required-constructor-parameter/source-code-before',
                $pathToFixtures . '/new-required-constructor-parameter/source-code-after',
                [
                    'Class (MINOR)',
                    'Test\Vcs\TestClass::__construct | [public] Added a required constructor object parameter. | M103'
                ],
                'Minor change is detected.'
            ],
            'api-class-new-required-constructor-parameter-for-extendable' => [
                $pathToFixtures . '/new-required-constructor-parameter-for-extendable/source-code-before',
                $pathToFixtures . '/new-required-constructor-parameter-for-extendable/source-code-after',
                [
                    'Class (MINOR)',
                    'Magento\Framework\Model\AbstractExtensibleModel::__construct | [public] Added a required constructor object parameter. | M103'
                ],
                'Minor change is detected.'
            ],
            'api-class-new-required-scalar-constructor-parameter-for-extendable' => [
                $pathToFixtures . '/new-required-scalar-constructor-parameter-for-extendable/source-code-before',
                $pathToFixtures . '/new-required-scalar-constructor-parameter-for-extendable/source-code-after',
                [
                    'Class (MAJOR)',
                    'Magento\Framework\Model\AbstractExtensibleModel::__construct | [public] Method parameter added. | V010'
                ],
                'Major change is detected.'
            ],
            'api-class-new-optional-scalar-constructor-parameter-for-extendable' => [
                $pathToFixtures . '/new-optional-scalar-constructor-parameter-for-extendable/source-code-before',
                $pathToFixtures . '/new-optional-scalar-constructor-parameter-for-extendable/source-code-after',
                [
                    'Class (MINOR)',
                    'Magento\Framework\Model\AbstractExtensibleModel::__construct | [public] Added an optional constructor parameter to extendable @api class. | M111'
                ],
                'Minor change is detected.'
            ],
            'api-class-new-optional-constructor-parameter-for-extendable' => [
                $pathToFixtures . '/new-optional-constructor-parameter-for-extendable/source-code-before',
                $pathToFixtures . '/new-optional-constructor-parameter-for-extendable/source-code-after',
                [
                    'Class (MINOR)',
                    'Magento\Framework\Model\AbstractExtensibleModel::__construct | [public] Added an optional constructor parameter to extendable @api class. | M111'
                ],
                'Minor change is detected.'
            ],
            'api-class-new-required-scalar-constructor-parameter' => [
                $pathToFixtures . '/new-required-scalar-constructor-parameter/source-code-before',
                $pathToFixtures . '/new-required-scalar-constructor-parameter/source-code-after',
                [
                    'Class (MAJOR)',
                    'Test\Vcs\TestClass::__construct | [public] Method parameter added. | V010'
                ],
                'Major change is detected.'
            ],
            'api-class-new-optional-constructor-parameter' => [
                $pathToFixtures . '/new-optional-constructor-parameter/source-code-before',
                $pathToFixtures . '/new-optional-constructor-parameter/source-code-after',
                [
                    'Class (PATCH)',
                    'Test\Vcs\TestClass::__construct | [public] Added an optional constructor parameter. | M112'
                ],
                'Patch change is detected.'
            ],
            'api-class-removed-non-last-constructor-parameter' => [
                $pathToFixtures . '/removed-non-last-constructor-parameter/source-code-before',
                $pathToFixtures . '/removed-non-last-constructor-parameter/source-code-after',
                [
                    'Class (MAJOR)',
                    'Test\Vcs\TestClass::__construct | [public] Method parameter removed. | V082'
                ],
                'Major change is detected.'
            ],
            'api-class-removed-last-constructor-parameter' => [
                $pathToFixtures . '/removed-last-constructor-parameter/source-code-before',
                $pathToFixtures . '/removed-last-constructor-parameter/source-code-after',
                [
                    'Class (PATCH)',
                    'Test\Vcs\TestClass::__construct | [public] Removed last constructor parameter(s). | M101'
                ],
                'Patch change is detected.'
            ],
            'api-class-removed-required-last-method-parameter' => [
                $pathToFixtures . '/removed-required-last-method-parameter/source-code-before',
                $pathToFixtures . '/removed-required-last-method-parameter/source-code-after',
                [
                    'Class (MINOR)',
                    'Test\Vcs\TestClass::publicMethod    | [public] Removed last method parameter(s).    | M100',
                    'Test\Vcs\TestClass::protectedMethod | [protected] Removed last method parameter(s). | M100'
                ],
                'Minor change is detected.'
            ],
            'api-class-changed-method-parameter-type' => [
                $pathToFixtures . '/changed-method-parameter-type/source-code-before',
                $pathToFixtures . '/changed-method-parameter-type/source-code-after',
                [
                    'Class (MAJOR)',
                    'Test\Vcs\TestClass::testMethod | [public] Method parameter typing changed. | M113'
                ],
                'Major change is detected.'
            ],
            'api-class-changed-method-return-type' => [
                $pathToFixtures . '/changed-method-return-type/source-code-before',
                $pathToFixtures . '/changed-method-return-type/source-code-after',
                [
                    'Class (MAJOR)',
                    'Test\Vcs\TestClass::declarationAddedPublic         | [public] Method return typing changed.    | M120 ',
                    'Test\Vcs\TestClass::declarationAddedProtected      | [protected] Method return typing changed. | M121 ',
                    'Test\Vcs\TestClass::declarationChangedPublic       | [public] Method return typing changed.    | M120 ',
                    'Test\Vcs\TestClass::annotationChangedPublic        | [public] Method return typing changed.    | M120 ',
                    'Test\Vcs\TestClass::declarationChangedProtected    | [protected] Method return typing changed. | M121 ',
                    'Test\Vcs\TestClass::annotationChangedProtected     | [protected] Method return typing changed. | M121 ',
                    'Test\Vcs\TestClass::declarationRemovedPublic       | [public] Method return typing changed.    | M120 ',
                    'Test\Vcs\TestClass::annotationRemovedPublic        | [public] Method return typing changed.    | M120 ',
                    'Test\Vcs\TestClass::declarationRemovedProtected    | [protected] Method return typing changed. | M121 ',
                    'Test\Vcs\TestClass::annotationRemovedProtected     | [protected] Method return typing changed. | M121 ',
                    'Test\Vcs\TestClass::php7RemoveAnnotationWithoutDoc | [public] Method return typing changed.    | M120 ',
                    'Test\Vcs\TestClass::declarationAddedPrivate        | [private] Method return typing changed.   | M122 ',
                    'Test\Vcs\TestClass::declarationChangedPrivate      | [private] Method return typing changed.   | M122 ',
                    'Test\Vcs\TestClass::annotationChangedPrivate       | [private] Method return typing changed.   | M122 ',
                    'Test\Vcs\TestClass::declarationRemovedPrivate      | [private] Method return typing changed.   | M122 ',
                    'Test\Vcs\TestClass::annotationRemovedPrivate       | [private] Method return typing changed.   | M122 ',
                    'Test\Vcs\TestClass::toBeChangedClassReturnTypeInlineDeclaration         | [public] Method return typing changed.    | M120',
                    'Test\Vcs\TestClass::nullableToBeChangedClassReturnTypeInlineDeclaration | [public] Method return typing changed.    | M120'
                ],
                'Major change is detected.',
                [
                    'Test\Vcs\TestClass::declarationFcqnNotChangedPublic         | [public] Method return typing changed.    | M120 ',
                    'Test\Vcs\TestClass::declarationSelfNotChangedProtected      | [protected] Method return typing changed. | M121 ',
                    'Test\Vcs\TestClass::classReturnTypeInlineDeclaration         | [public] Method return typing changed.    | M120 ',
                    'Test\Vcs\TestClass::nullableClassReturnTypeInlineDeclaration | [public] Method return typing changed.    | M120 '
                ]
            ],
            'api-class-new-method-parameter-type' => [
                $pathToFixtures . '/new-method-parameter-type/source-code-before',
                $pathToFixtures . '/new-method-parameter-type/source-code-after',
                [
                    'Class (MAJOR)',
                    'Test\Vcs\TestClass::testMethod | [public] Method parameter typing added. | V085'
                ],
                'Major change is detected.'
            ],
            'api-class-removed-method-parameter-type' => [
                $pathToFixtures . '/removed-method-parameter-type/source-code-before',
                $pathToFixtures . '/removed-method-parameter-type/source-code-after',
                [
                    'Class (MAJOR)',
                    'Test\Vcs\TestClass::testMethod | [public] Method parameter typing removed. | V088'
                ],
                'Major change is detected.'
            ],
            'api-class-constant-added' => [
                $pathToFixtures . '/constant-added/source-code-before',
                $pathToFixtures . '/constant-added/source-code-after',
                [
                    'Class (MINOR)',
                    'Test\Vcs\TestClass::SOME_NEW_CONSTANCE | Constant has been added. | M071'
                ],
                'Minor change is detected.'
            ],
            'api-class-constant-removed' => [
                $pathToFixtures . '/constant-removed/source-code-before',
                $pathToFixtures . '/constant-removed/source-code-after',
                [
                    'Class (MAJOR)',
                    'Test\Vcs\TestClass::SOME_NEW_CONSTANCE | Constant has been removed. | M073'
                ],
                'Major change is detected.'
            ],
            'api-class-remove-extends' => [
                $pathToFixtures . '/remove-extends/source-code-before',
                $pathToFixtures . '/remove-extends/source-code-after',
                [
                    'Class (MAJOR)',
                    'Test\Vcs\TestClass | Extends has been removed. | M0122'
                ],
                'Major change is detected.'
            ],
            'api-class-remove-implements' => [
                $pathToFixtures . '/remove-implements/source-code-before',
                $pathToFixtures . '/remove-implements/source-code-after',
                [
                    'Class (MAJOR)',
                    'Test\Vcs\TestClass | Implements has been removed. | M0123'
                ],
                'Major change is detected.'
            ],
            'api-class-added-extends' => [
                $pathToFixtures . '/added-extends/source-code-before',
                $pathToFixtures . '/added-extends/source-code-after',
                [
                    'Class (MINOR)',
                    'Test\Vcs\TestClass | Parent has been added. | M0124'
                ],
                'Minor change is detected.'
            ],
            'api-class-added-implements' => [
                $pathToFixtures . '/added-implements/source-code-before',
                $pathToFixtures . '/added-implements/source-code-after',
                [
                    'Class (MINOR)',
                    'Test\Vcs\TestClass1 | Interface has been added. | M0125',
                    'Test\Vcs\TestClass2 | Interface has been added. | M0125'
                ],
                'Minor change is detected.'
            ],
            'api-class-added-trait' => [
                $pathToFixtures . '/added-trait/source-code-before',
                $pathToFixtures . '/added-trait/source-code-after',
                [
                    'Class (MINOR)',
                    'Test\Vcs\TestClass1 | New trait has been used. | M0126',
                    'Test\Vcs\TestClass2 | New trait has been used. | M0126'
                ],
                'Minor change is detected.'
            ],
            'api-class-exception-superclassed' => [
                $pathToFixtures . '/exception-superclassed/source-code-before',
                $pathToFixtures . '/exception-superclassed/source-code-after',
                [
                    'Class (MAJOR)',
                    'Test\Vcs\TestClass::exceptionSuperclassed | [public] Exception has been superclassed. | M127'
                ],
                'Major change is detected.'
            ],
            'api-class-exception-subclassed' => [
                $pathToFixtures . '/exception-subclassed/source-code-before',
                $pathToFixtures . '/exception-subclassed/source-code-after',
                [
                    'Class (MINOR)',
                    'Test\Vcs\TestClass::exceptionSubclassed | [public] Exception has been subclassed. | M129'
                ],
                'Minor change is detected.'
            ],
            'api-class-exception-superclass-added' => [
                $pathToFixtures . '/exception-superclass-added/source-code-before',
                $pathToFixtures . '/exception-superclass-added/source-code-after',
                [
                    'Class (MAJOR)',
                    'Test\Vcs\TestClass::exceptionSuperclassAdded | [public] Superclassed Exception has been added. | M131'
                ],
                'Major change is detected.'
            ],
            'api-class-exception-subclass-added' => [
                $pathToFixtures . '/exception-subclass-added/source-code-before',
                $pathToFixtures . '/exception-subclass-added/source-code-after',
                [
                    'Suggested semantic versioning change: NONE'
                ],
                'Patch change is detected.'
            ],
            'api-moved-method-parameter-type-from-docblock-to-inline' => [
                $pathToFixtures . '/moved-method-parameter-type-from-docblock-to-inline/source-code-before',
                $pathToFixtures . '/moved-method-parameter-type-from-docblock-to-inline/source-code-after',
                [
                    'Class (MAJOR)',
                    'Test\Vcs\TestClass::movedNativeTypePublic    | [public] Method parameter typehint was moved from doc block annotation to in-line.    | M134',
                    'Test\Vcs\TestClass::movedNonNativeTypePublic | [public] Method parameter typehint was moved from doc block annotation to in-line.    | M134',
                    'Test\Vcs\TestClass::movedNativeTypeProtected | [protected] Method parameter typehint was moved from doc block annotation to in-line. | M152',
                    'Test\Vcs\TestClass::movedNativeTypePrivate   | [private] Method parameter typehint was moved from doc block annotation to in-line.   | M164',
                ],
                'Major change is detected.'
            ],
            'api-moved-method-parameter-type-from-inline-to-docblock' => [
                $pathToFixtures . '/moved-method-parameter-type-from-inline-to-docblock/source-code-before',
                $pathToFixtures . '/moved-method-parameter-type-from-inline-to-docblock/source-code-after',
                [
                    'Class (MAJOR)',
                    'Test\Vcs\TestClass::movedNativeTypePublic    | [public] Method parameter typehint was moved from in-line to doc block annotation.    | M135',
                    'Test\Vcs\TestClass::movedNonNativeTypePublic | [public] Method parameter typehint was moved from in-line to doc block annotation.    | M135',
                    'Test\Vcs\TestClass::movedNativeTypeProtected | [protected] Method parameter typehint was moved from in-line to doc block annotation. | M154',
                    'Test\Vcs\TestClass::movedNativeTypePrivate   | [private] Method parameter typehint was moved from in-line to doc block annotation.   | M166',
                ],
                'Major change is detected.'
            ],
            'api-moved-method-return-type-from-docblock-to-inline' => [
                $pathToFixtures . '/moved-method-return-type-from-docblock-to-inline/source-code-before',
                $pathToFixtures . '/moved-method-return-type-from-docblock-to-inline/source-code-after',
                [
                    'Class (MAJOR)',
                    'Test\Vcs\TestClass::movedNativeTypePublic    | [public] Method return typehint was moved from doc block annotation to in-line.    | M136',
                    'Test\Vcs\TestClass::movedNonNativeTypePublic | [public] Method return typehint was moved from doc block annotation to in-line.    | M136',
                    'Test\Vcs\TestClass::movedNativeTypeProtected | [protected] Method return typehint was moved from doc block annotation to in-line. | M156',
                    'Test\Vcs\TestClass::movedNativeTypePrivate   | [private] Method return typehint was moved from doc block annotation to in-line.   | M168',
                ],
                'Major change is detected.'
            ],
            'api-moved-method-return-type-from-inline-to-docblock' => [
                $pathToFixtures . '/moved-method-return-type-from-inline-to-docblock/source-code-before',
                $pathToFixtures . '/moved-method-return-type-from-inline-to-docblock/source-code-after',
                [
                    'Class (MAJOR)',
                    'Test\Vcs\TestClass::movedNativeTypePublic    | [public] Method return typehint was moved from in-line to doc block annotation.    | M137',
                    'Test\Vcs\TestClass::movedNonNativeTypePublic | [public] Method return typehint was moved from in-line to doc block annotation.    | M137',
                    'Test\Vcs\TestClass::movedNativeTypeProtected | [protected] Method return typehint was moved from in-line to doc block annotation. | M158',
                    'Test\Vcs\TestClass::movedNativeTypePrivate   | [private] Method return typehint was moved from in-line to doc block annotation.   | M170',
                ],
                'Major change is detected.'
            ],
            'api-moved-method-variable-type-from-docblock-to-inline' => [
                $pathToFixtures . '/moved-method-variable-type-from-docblock-to-inline/source-code-before',
                $pathToFixtures . '/moved-method-variable-type-from-docblock-to-inline/source-code-after',
                [
                    'Class (MAJOR)',
                    'Test\Vcs\TestClass::movedNativeTypePublic    | [public] Method variable typehint was moved from doc block annotation to in-line.    | M146',
                    'Test\Vcs\TestClass::movedNonNativeTypePublic | [public] Method variable typehint was moved from doc block annotation to in-line.    | M146',
                    'Test\Vcs\TestClass::movedNativeTypeProtected | [protected] Method variable typehint was moved from doc block annotation to in-line. | M160',
                    'Test\Vcs\TestClass::movedNativeTypePrivate   | [private] Method variable typehint was moved from doc block annotation to in-line.   | M172',
                ],
                'Major change is detected.'
            ],
            'api-moved-method-variable-type-from-inline-to-docblock' => [
                $pathToFixtures . '/moved-method-variable-type-from-inline-to-docblock/source-code-before',
                $pathToFixtures . '/moved-method-variable-type-from-inline-to-docblock/source-code-after',
                [
                    'Class (MAJOR)',
                    'Test\Vcs\TestClass::movedNativeTypePublic    | [public] Method variable typehint was moved from in-line to doc block annotation.    | M149',
                    'Test\Vcs\TestClass::movedNonNativeTypePublic | [public] Method variable typehint was moved from in-line to doc block annotation.    | M149',
                    'Test\Vcs\TestClass::movedNativeTypeProtected | [protected] Method variable typehint was moved from in-line to doc block annotation. | M162',
                    'Test\Vcs\TestClass::movedNativeTypePrivate   | [private] Method variable typehint was moved from in-line to doc block annotation.   | M174',
                ],
                'Major change is detected.'
            ],
            'api-class-added-method-subclass-overwrite' => [
                $pathToFixtures . '/added-method-subclass-overwrite/source-code-before',
                $pathToFixtures . '/added-method-subclass-overwrite/source-code-after',
                [
                    'Class (PATCH)',
                    'Test\Vcs\ApiClass::testFunction | [public] Method overwrite has been added. | V028'
                ],
                'Patch change is detected.'
            ],
            'api-annotation-added-to-class' => [
                $pathToFixtures . '/annotation-added/source-code-before',
                $pathToFixtures . '/annotation-added/source-code-after',
                [
                    'Class (MINOR)',
                    'Test\Vcs\TestClass | @api annotation has been added. | M0141',
                ],
                'Minor change is detected.',
            ],
            'api-annotation-removed-from-class' => [
                $pathToFixtures . '/annotation-removed/source-code-before',
                $pathToFixtures . '/annotation-removed/source-code-after',
                [
                    'Class (MAJOR)',
                    'Test\Vcs\TestClass | @api annotation has been removed. | M0142',
                ],
                'Major change is detected.',
            ],
            'api-annotation-not-changed' => [
                $pathToFixtures . '/annotation-not-changed/source-code-before',
                $pathToFixtures . '/annotation-not-changed/source-code-after',
                [
                    'Suggested semantic versioning change: NONE',
                ],
                'Patch change is detected.',
            ],
            'api-class-added-method-subclass-overwrite-transition' => [
                $pathToFixtures . '/added-method-subclass-overwrite-transition/source-code-before',
                $pathToFixtures . '/added-method-subclass-overwrite-transition/source-code-after',
                [
                    'Class (PATCH)',
                    'Test\Vcs\ApiClass::testFunction          | [public] Method overwrite has been added.    | V028 |',
                    'Test\Vcs\ApiClass::testFunctionProtected | [protected] Method overwrite has been added. | V028 |',
                    'Test\Vcs\ApiClass::__construct           | [public] Method overwrite has been added.    | V028 |',
                    'Test\Vcs\ApiClass::__destruct            | [public] Method overwrite has been added.    | V028 |',
                    'Test\Vcs\ApiClass::__call                | [public] Method overwrite has been added.    | V028 |',
                    'Test\Vcs\ApiClass::__callStatic          | [public] Method overwrite has been added.    | V028 |',
                    'Test\Vcs\ApiClass::__get                 | [public] Method overwrite has been added.    | V028 |',
                    'Test\Vcs\ApiClass::__set                 | [public] Method overwrite has been added.    | V028 |',
                    'Test\Vcs\ApiClass::__isset               | [public] Method overwrite has been added.    | V028 |',
                    'Test\Vcs\ApiClass::__unset               | [public] Method overwrite has been added.    | V028 |',
                    'Test\Vcs\ApiClass::__sleep               | [public] Method overwrite has been added.    | V028 |',
                    'Test\Vcs\ApiClass::__wakeup              | [public] Method overwrite has been added.    | V028 |',
                    'Test\Vcs\ApiClass::__serialize           | [public] Method overwrite has been added.    | V028 |',
                    'Test\Vcs\ApiClass::__unserialize         | [public] Method overwrite has been added.    | V028 |',
                    'Test\Vcs\ApiClass::__toString            | [public] Method overwrite has been added.    | V028 |',
                    'Test\Vcs\ApiClass::__invoke              | [public] Method overwrite has been added.    | V028 |',
                    'Test\Vcs\ApiClass::__set_state           | [public] Method overwrite has been added.    | V028 |',
                    'Test\Vcs\ApiClass::__clone               | [public] Method overwrite has been added.    | V028 |',
                    'Test\Vcs\ApiClass::__debugInfo           | [public] Method overwrite has been added.    | V028 |',
                ],
                'Patch change is detected.'
            ],
            'protected-property-overwrite' => [
                $pathToFixtures . '/protected-property-overwrite/source-code-before',
                $pathToFixtures . '/protected-property-overwrite/source-code-after',
                [
                    'Class (PATCH)',
                    'Test\Vcs\TestClass::$_cacheTag | [protected] Property overwrite has been added. | M020'
                ],
                'Patch change is detected.'
            ],
            'public-property-overwrite' => [
                $pathToFixtures . '/public-property-overwrite/source-code-before',
                $pathToFixtures . '/public-property-overwrite/source-code-after',
                [
                    'Class (PATCH)',
                    'Test\Vcs\TestClass::$_cacheTag | [public] Property overwrite has been added. | M019'
                ],
                'Patch change is detected.'
            ],
        ];
    }
}
