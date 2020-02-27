<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Test\Unit;

use Magento\SemanticVersionChecker\InjectableReport;
use Magento\SemanticVersionChecker\MergedReport;
use Magento\SemanticVersionChecker\Operation\ClassConstantRemoved;
use Magento\SemanticVersionChecker\Operation\ClassConstructorOptionalParameterAdded;
use Magento\SemanticVersionChecker\Operation\ClassExtendsAdded;
use Magento\SemanticVersionChecker\Operation\ClassImplementsAdded;
use Magento\SemanticVersionChecker\Operation\ClassMethodOptionalParameterAdded;
use Magento\SemanticVersionChecker\Operation\ClassTraitAdded;
use Magento\SemanticVersionChecker\Operation\DropForeignKey;
use Magento\SemanticVersionChecker\Operation\SystemXml\FieldAdded;
use PHPSemVerChecker\Report\Report;
use PHPSemVerChecker\SemanticVersioning\Level;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MergedReportTest extends TestCase
{
    /**
     * Verify that reports are properly merged including non-standard contexts
     *
     * @return void
     */
    public function testMergedChanges()
    {
        $diffsWithDatabase = [];
        $diffsWithDatabase['class'][Level::PATCH] = [$this->createMock(ClassConstructorOptionalParameterAdded::class)];
        $diffsWithDatabase['class'][Level::MINOR] = [
            $this->createMock(ClassExtendsAdded::class),
            $this->createMock(ClassMethodOptionalParameterAdded::class)
        ];
        $diffsWithDatabase['database'][Level::MAJOR] = [$this->createMock(DropForeignKey::class)];
        $databaseReport = new InjectableReport($diffsWithDatabase);

        $diffsWithXml = [];
        $diffsWithXml['class'][Level::MAJOR] = [$this->createMock(ClassConstantRemoved::class)];
        $diffsWithXml['class'][Level::MINOR] = [$this->createMock(ClassImplementsAdded::class)];
        $diffsWithXml['xml'][Level::MINOR] = [$this->createMock(FieldAdded::class)];
        $xmlReport = new InjectableReport($diffsWithXml);

        /** @var MockObject|ClassTraitAdded $preMergeOperation */
        $preMergeOperation = $this->createMock(ClassTraitAdded::class);
        $preMergeOperation->expects($this->any())->method('getLevel')->willReturn(Level::MINOR);

        $mergedReport = new MergedReport();
        $mergedReport->addClass($preMergeOperation);
        $mergedReport->merge($databaseReport);
        $mergedReport->merge($xmlReport);

        $mergedDiffs = $mergedReport->getDifferences();
        $this->assertTrue(key_exists('database', $mergedDiffs));
        $this->assertTrue(key_exists('xml', $mergedDiffs));
        $this->assertTrue($this->hasAllDifferences($mergedReport, $databaseReport));
        $this->assertTrue($this->hasAllDifferences($mergedReport, $xmlReport));
        $this->assertTrue(array_search($preMergeOperation, $mergedDiffs['class'][Level::MINOR]) !== false);
    }

    /**
     * Checks if a merged report contains all differences in another report

     * @param Report $mergedReport
     * @param Report $inputReport
     * @return bool
     */
    private function hasAllDifferences($mergedReport, $inputReport)
    {
        $mergedDiffs = $mergedReport->getDifferences();
        $inputDiffs = $inputReport->getDifferences();
        foreach ($inputDiffs as $context => $levels) {
            if (!key_exists($context, $mergedDiffs)) {
                return false;
            }
            $mergedLevels = $mergedDiffs[$context];
            foreach ($levels as $level => $operations) {
                if (!key_exists($level, $mergedLevels)) {
                    return false;
                }
                $mergedOperations = $mergedLevels[$level];
                foreach ($operations as $operation) {
                    if (array_search($operation, $mergedOperations) === false) {
                        return false;
                    }
                }
            }
        }
        return true;
    }
}
