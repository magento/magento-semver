<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Analyzer;

use PhpParser\Node\Stmt;
use PHPSemVerChecker\Registry\Registry;
use PHPSemVerChecker\Report\Report;
use PHPSemVerChecker\SemanticVersioning\Level;
use Magento\SemanticVersionChecker\Operation\EtSchema\EtSchemaOperation;

/**
 * Class EtSchemaAnalyzer analyzes changes in et_schema.xml
 */
class EtSchemaAnalyzer implements AnalyzerInterface
{

    /**
     * Analyser context
     */
    public const CONTEXT = 'etSchema';

    /**
     * @var array of actions
     */
    private static $actions = [
        'addedRecord' => [
            'level' => Level::MINOR,
            'code' => 'T004',
            'message' => 'Added a new declaration for record %s.'
        ],
        'removedRecord' => [
            'level' => Level::MAJOR,
            'code' => 'T001',
            'message' => 'Removed declaration for type %s.'
        ],
        'addedField' => [
            'level' => Level::PATCH,
            'code' => 'T005',
            'message' => 'Added field %s to type %s.'
        ],
        'removedField' => [
            'level' => Level::MAJOR,
            'code' => 'T002',
            'message' => 'Removed field %s from type %s.'
        ],
        'changedField' => [
            'level' => Level::MAJOR,
            'code' => 'T003',
            'message' => 'Changed field %s declaration in type %s.'
        ]
    ];

    /**
     * @var Report
     */
    private $report;

    /**
     * Constructor.
     *
     * @param Report $report
     */
    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    /**
     * Process a new configuration file
     *
     * @param array $moduleConfig
     * @return array
     */
    private function addedModuleConfig(array $moduleConfig): array
    {
        $changes = [];
        foreach ($moduleConfig as $moduleName => $records) {
            foreach ($records as $record) {
                $changes[] = $this->addedRecord($moduleName, $record['name']);
            }
        }
        return $changes;
    }

    /**
     * Process removed configuration file
     *
     * @param array $moduleConfig
     * @return array
     */
    private function removedModuleConfig(array $moduleConfig): array
    {
        $changes = [];
        foreach ($moduleConfig as $moduleName => $records) {
            foreach ($records as $record) {
                $changes[] = $this->removedRecord($moduleName, $record['name']);
            }
        }
        return $changes;
    }

    /**
     * Register record creation
     *
     * @param string $moduleName
     * @param string $recordName
     * @return array
     */
    private function addedRecord(string $moduleName, string $recordName): array
    {
        return [
            'level' => self::$actions[__FUNCTION__]['level'],
            'code' => self::$actions[__FUNCTION__]['code'],
            'location' => sprintf('urn:magento:module:%s:etc/et_schema.xml %s', $moduleName, $recordName),
            'target' => $recordName,
            'reason' => sprintf(self::$actions[__FUNCTION__]['message'], $recordName)
        ];
    }

    /**
     * Register record removal
     *
     * @param string $moduleName
     * @param string $recordName
     * @return array
     */
    private function removedRecord(string $moduleName, string $recordName): array
    {
        return [
            'level' => self::$actions[__FUNCTION__]['level'],
            'code' => self::$actions[__FUNCTION__]['code'],
            'location' => sprintf('urn:magento:module:%s:etc/et_schema.xml %s', $moduleName, $recordName),
            'target' => $recordName,
            'reason' => sprintf(self::$actions[__FUNCTION__]['message'], $recordName)
        ];
    }

    /**
     * Register removed field
     *
     * @param string $moduleName
     * @param string $recordName
     * @param string $fieldName
     * @return array
     */
    private function removedField(string $moduleName, string $recordName, string $fieldName): array
    {
        return [
            'level' => self::$actions[__FUNCTION__]['level'],
            'code' => self::$actions[__FUNCTION__]['code'],
            'location' => sprintf(
                'urn:magento:module:%s:etc/et_schema.xml %s:%s',
                $moduleName,
                $recordName,
                $fieldName
            ),
            'target' => $recordName,
            'reason' => sprintf(self::$actions[__FUNCTION__]['message'], $fieldName, $recordName)
        ];
    }

    /**
     * Register a new field
     *
     * @param string $moduleName
     * @param string $recordName
     * @param string $fieldName
     * @return array
     */
    private function addedField(string $moduleName, string $recordName, string $fieldName): array
    {
        return [
            'level' => self::$actions[__FUNCTION__]['level'],
            'code' => self::$actions[__FUNCTION__]['code'],
            'location' => sprintf(
                'urn:magento:module:%s:etc/et_schema.xml %s:%s',
                $moduleName,
                $recordName,
                $fieldName
            ),
            'target' => $recordName,
            'reason' => sprintf(self::$actions[__FUNCTION__]['message'], $fieldName, $recordName)
        ];
    }

    /**
     * Register field change
     *
     * @param string $moduleName
     * @param string $recordName
     * @param string $fieldName
     * @return array
     */
    private function changedField(string $moduleName, string $recordName, string $fieldName): array
    {
        return [
            'level' => self::$actions[__FUNCTION__]['level'],
            'code' => self::$actions[__FUNCTION__]['code'],
            'location' => sprintf(
                'urn:magento:module:%s:etc/et_schema.xml %s:%s',
                $moduleName,
                $recordName,
                $fieldName
            ),
            'target' => $recordName,
            'reason' => sprintf(self::$actions[__FUNCTION__]['message'], $fieldName, $recordName)
        ];
    }

    /**
     * Analyze record structure
     *
     * @param string $moduleName
     * @param $beforeRecord
     * @param $afterRecord
     * @return array
     */
    private function analyzeRecord(string $moduleName, $beforeRecord, $afterRecord): array
    {
        $changes = [];
        $commonFields = array_intersect(
            array_keys($beforeRecord['field']),
            array_keys($afterRecord['field'])
        );
        foreach ($commonFields as $fieldName) {
            if (
                $beforeRecord['field'][$fieldName]['type'] != $afterRecord['field'][$fieldName]['type']
                || $beforeRecord['field'][$fieldName]['repeated'] != $afterRecord['field'][$fieldName]['repeated']
            ) {
                $changes[] = $this->changedField($moduleName, $beforeRecord['name'], $fieldName);
            }
        }
        $diff = array_merge(
            array_diff(
                array_keys($beforeRecord['field']),
                array_keys($afterRecord['field'])
            ),
            array_diff(
                array_keys($afterRecord['field']),
                array_keys($beforeRecord['field'])
            )
        );
        foreach ($diff as $fieldName) {
            if (isset($beforeRecord['field'][$fieldName])) {
                $changes[] = $this->removedField($moduleName, $beforeRecord['name'], $fieldName);
            } else {
                $changes[] = $this->addedField($moduleName, $afterRecord['name'], $fieldName);
            }
        }
        return $changes;
    }

    /**
     * Analyze module configuration file
     *
     * @param string $moduleName
     * @param array $beforeModuleConfig
     * @param array $afterModuleConfig
     * @return array
     */
    private function analyzeModuleConfig(string $moduleName, array $beforeModuleConfig, array $afterModuleConfig): array
    {
        $changes = [];
        $commonRecords = array_intersect(
            array_keys($beforeModuleConfig),
            array_keys($afterModuleConfig)
        );
        foreach ($commonRecords as $recordName) {
            $changes = array_merge(
                $changes,
                $this->analyzeRecord(
                    $moduleName,
                    $beforeModuleConfig[$recordName],
                    $afterModuleConfig[$recordName]
                )
            );
        }
        $diff = array_merge(
            array_diff(
                array_keys($beforeModuleConfig),
                array_keys($afterModuleConfig)
            ),
            array_diff(
                array_keys($afterModuleConfig),
                array_keys($beforeModuleConfig)
            )
        );
        foreach ($diff as $recordName) {
            if (isset($beforeModuleConfig[$recordName])) {
                $changes[] = $this->removedRecord($moduleName, $recordName);
            } else {
                $changes[] = $this->addedRecord($moduleName, $recordName);
            }
        }
        return $changes;
    }

    /**
     * Register changes to the report
     *
     * @param array $changes
     */
    public function reportChanges(array $changes): void
    {
        foreach ($changes as $change) {
            $this->report->add(
                self::CONTEXT,
                new EtSchemaOperation(
                    $change['location'],
                    $change['code'],
                    $change['target'],
                    $change['reason'],
                    $change['level']
                )
            );
        }
    }

    /**
     * Analyze configuration changes
     *
     * @param Stmt|Registry $registryBefore
     * @param Stmt|Registry $registryAfter
     * @return Report
     */
    public function analyze($registryBefore, $registryAfter)
    {
        $before = isset($registryBefore->data[self::CONTEXT]) ? $registryBefore->data[self::CONTEXT] : [];
        $after = isset($registryAfter->data[self::CONTEXT]) ? $registryAfter->data[self::CONTEXT] : [];
        $changes = [];
        $commonModules = array_intersect(array_keys($before), array_keys($after));
        foreach ($commonModules as $moduleName) {
            $changes = array_merge(
                $changes,
                $this->analyzeModuleConfig(
                    $moduleName,
                    $before[$moduleName],
                    $after[$moduleName]
                )
            );
        }

        $changes = array_merge(
            $changes,
            $this->removedModuleConfig(
                array_intersect_key($before, array_flip(array_diff(array_keys($before), array_keys($after))))
            )
        );

        $changes = array_merge(
            $changes,
            $this->addedModuleConfig(
                array_intersect_key($after, array_flip(array_diff(array_keys($after), array_keys($before))))
            )
        );

        $this->reportChanges($changes);
        return $this->report;
    }
}
