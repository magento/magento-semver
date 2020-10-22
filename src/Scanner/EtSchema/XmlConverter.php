<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SemanticVersionChecker\Scanner\EtSchema;

/**
 * Class XmlConverter
 */
class XmlConverter
{
    /**
     * Converts XML node into corresponding array.
     *
     * @param \DOMNode $source
     * @return array|string
     */
    private function convertNode(\DOMNode $source)
    {
        $result = [];
        if ($source->hasAttributes()) {
            $attrs = $source->attributes;
            foreach ($attrs as $attr) {
                $result[$attr->name] = $attr->value;
            }
        }
        if ($source->hasChildNodes()) {
            $children = $source->childNodes;
            if ($children->length == 1) {
                $child = $children->item(0);
                if ($child->nodeType == XML_TEXT_NODE) {
                    $result['_value'] = $child->nodeValue;
                    return count($result) == 1 ? $result['_value'] : $result;
                }
            }
            foreach ($children as $child) {
                if ($child instanceof \DOMCharacterData) {
                    continue;
                }
                $result[$child->nodeName][] = $this->convertNode($child);
            }
        }
        return $result;
    }

    private function structureRecords(array $configData): array
    {
        if (!isset($configData['config'][0]['record'])) {
            return [];
        }
        $records = [];
        foreach ($configData['config'][0]['record'] as $queryData) {
            $records[$queryData['name']] = [
                'name' => $queryData['name'],
            ];
            $idField = null;
            foreach ($queryData['field'] as $fieldData) {
                $field = [
                    'name' => $fieldData['name'],
                    'type' => $fieldData['type'],
                    'provider' => isset($fieldData['provider']) ? $fieldData['provider'] : null,
                    'repeated' => (isset($fieldData['repeated']) && $fieldData['repeated'] == "true") ? true : false
                ];
                if ($fieldData['type'] == 'ID') {
                    $idField = $fieldData['name'];
                }
                if (isset($fieldData['provider'])) {
                    if (isset($fieldData['using'])) {
                        foreach ($fieldData['using'] as $usingField) {
                            $field['using'][$usingField['field']] = $usingField;
                        }
                    } else {
                        $field['using'][$idField] = ['field' => $idField];
                    }
                }
                $records[$queryData['name']]['field'][$field['name']] = $field;
            }
            $records[$queryData['name']]['ID'] = $idField;
        }
        return $records;
    }

    /**
     * Converts XML document into corresponding array.
     *
     * @param \DOMDocument $source
     * @return array
     */
    public function convert(\DOMNode $source): array
    {
        $configData = $this->convertNode($source);
        return $this->structureRecords($configData);
    }
}
