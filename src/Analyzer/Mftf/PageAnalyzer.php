<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SemanticVersionChecker\Analyzer\Mftf;

use Magento\SemanticVersionChecker\MftfReport;
use Magento\SemanticVersionChecker\Operation\Mftf\Page\PageAdded;
use Magento\SemanticVersionChecker\Operation\Mftf\Page\PageRemove;
use Magento\SemanticVersionChecker\Operation\Mftf\Page\PageSectionAdded;
use Magento\SemanticVersionChecker\Operation\Mftf\Page\PageSectionRemove;
use Magento\SemanticVersionChecker\Scanner\MftfScanner;
use PHPSemVerChecker\Registry\Registry;
use PHPSemVerChecker\Report\Report;

class PageAnalyzer extends AbstractEntityAnalyzer
{
    const MFTF_SECTION_ELEMENT = "{}section";
    const MFTF_DATA_TYPE = 'page';
    const MFTF_DATA_DIRECTORY = '/Mftf/Page/';

    /**
     * MFTF page.xml analyzer
     *
     * @param Registry $registryBefore
     * @param Registry $registryAfter
     * @return Report
     */
    public function analyze(Registry $registryBefore, Registry $registryAfter)
    {
        $beforeEntities = $registryBefore->data[MftfScanner::MFTF_ENTITY] ?? [];
        $afterEntities = $registryAfter->data[MftfScanner::MFTF_ENTITY] ?? [];

        foreach ($beforeEntities as $module => $entities) {
            $this->findAddedEntitiesInModule(
                $entities,
                $afterEntities[$module] ?? [],
                self::MFTF_DATA_TYPE,
                $this->getReport(),
                PageAdded::class,
                $module . '/Page'
            );
            foreach ($entities as $entityName => $beforeEntity) {
                if ($beforeEntity['type'] !== self::MFTF_DATA_TYPE) {
                    continue;
                }
                $operationTarget = $module . '/Page/' . $entityName;
                $filenames = implode(", ", $beforeEntity['filePaths']);

                // Validate page still exists
                if (!isset($afterEntities[$module][$entityName])) {
                    $operation = new PageRemove($filenames, $operationTarget);
                    $this->getReport()->add(MftfReport::MFTF_REPORT_CONTEXT, $operation);
                    continue;
                }

                // Sort Elements
                $beforeSectionElements = [];
                $afterSectionElements = [];

                foreach ($beforeEntity['value'] ?? [] as $beforeChild) {
                    if ($beforeChild['name'] == self::MFTF_SECTION_ELEMENT) {
                        $beforeSectionElements[] = $beforeChild;
                    }
                }
                foreach ($afterEntities[$module][$entityName]['value'] ?? [] as $afterChild) {
                    if ($afterChild['name'] == self::MFTF_SECTION_ELEMENT) {
                        $afterSectionElements[] = $afterChild;
                    }
                }

                // Validate <section> elements
                foreach ($beforeSectionElements as $beforeField) {
                    $beforeFieldKey = $beforeField['attributes']['name'];
                    $matchingElement = $this->findMatchingElement(
                        $beforeField,
                        $afterSectionElements,
                        'name'
                    );
                    if ($matchingElement === null) {
                        $operation = new PageSectionRemove($filenames, $operationTarget . '/' . $beforeFieldKey);
                        $this->getReport()->add(MftfReport::MFTF_REPORT_CONTEXT, $operation);
                    }
                }
                $this->findAddedElementsInArray(
                    $beforeSectionElements,
                    $afterSectionElements,
                    'name',
                    $this->getReport(),
                    $filenames,
                    PageSectionAdded::class,
                    $operationTarget
                );
            }
        }
        return $this->getReport();
    }
}
