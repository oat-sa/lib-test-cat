<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA
 */

namespace oat\libCat\ims\v1p3;

use oat\libCat\result\ItemResult;
use oat\libCat\result\ResultVariable;
use oat\dtms\DateTime;

class ResultFormatter
{
    /**
     * Format an array of result to provide to CAT a well formatted request body data
     *
     * @param array $data
     * @return string
     */
    static public function formatResultData(array $data)
    {
        if (isset($data['assessmentResult'])) {
            $itemResults = [];
            foreach ($data['assessmentResult']['itemResult'] as $key => $result) {
                if ($result instanceof ItemResult) {
                    $time = DateTime::createFromFormat('U.u', $result->getTimestamp());
                    $itemResult = [
                        'identifier' => $result->getItemRefId(),
                        //'sequenceIndex' => $key+1, //todo: check this value. maybe not required
                        'datestamp' => $time->format(DateTime::ISO8601),
                        'sessionStatus' => 'FINAL',//todo: check this value
                    ];
                    foreach ($result->getVariables() as $variable) {

                        $formattedVariable = self::formatVariable($variable);

                        switch ($variable->getVariableType()) {
                            case ResultVariable::TEMPLATE_VARIABLE:
                                $itemResult['templateVariables'][] = $formattedVariable;
                                break;
                            case ResultVariable::RESPONSE_VARIABLE:
                                $itemResult['responseVariables'][] = $formattedVariable;
                                break;
                            case ResultVariable::TRACE_VARIABLE:
                                $itemResult['traceVariables'][] = $formattedVariable;
                                break;
                            case ResultVariable::OUTCOME_VARIABLE:
                                $itemResult['outcomeVariables'][] = $formattedVariable;
                                break;
                        }
                    }

                    $itemResults[] = $itemResult;

                }
                $data['assessmentResult']['itemResult'] = $itemResults;
            }
        }

        return self::format($data);
    }

    /**
     * Format a $data to prepare it to send it to CAT server, consisting in a json_encode
     *
     * @param $data
     * @return string
     */
    static public function format($data)
    {
        return json_encode($data);
    }

    /**
     * Format a variable to have an array that represente a ResultVariable
     *
     * @param ResultVariable $variable
     * @param ItemResult $result
     * @return array
     */
    static protected function formatVariable(ResultVariable $variable)
    {
        $values = is_array($variable->getValue()) ? $variable->getValue() : [$variable->getValue()];
        $valueArray = [];
        foreach ($values as $val) {
            $valueArray[] = [
                "baseType" => strtoupper($variable->getType()),
                "value" => $val
            ];
        }
        return [
            'identifier' => $variable->getId(),
            'cardinality' => $variable->getCardinality(),
            'value' => $valueArray
        ];
    }

}