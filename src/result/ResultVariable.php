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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA
 */

namespace oat\libCat\result;

/**
 * Dataobject to represent a single variable
 *
 * @author Joel Bout, <joel@taotesting.com>
 */
class ResultVariable implements \JsonSerializable
{
    const OUTCOME_VARIABLE = 'outcome';
    const RESPONSE_VARIABLE = 'response';
    const TRACE_VARIABLE = 'trace';
    const TEMPLATE_VARIABLE = 'template';

    private $identifier;

    private $type;

    private $value;

    private $variableType;

    /**
     * Create a ResultVariable from the json array
     *
     * The given array is a serialization of Cat engine variable
     * It must contains the values and baseType key
     * Optionally the variableType can be present otherwise it will be evaluated as outcome variable
     *
     * @param array $array
     * @return \oat\libCat\result\ResultVariable
     */
    public static function restore($array)
    {
        $values = [];
        foreach ($array['values'] as $value) {
            $values[] = $value['valueString'];
        }
        if (count($values) == 1) {
            $values = reset($values);
        }
        $variableType = isset($array['variableType']) ? $array['variableType'] : self::OUTCOME_VARIABLE;
        $return = new static($array["identifier"], $value['baseType'], $values, $variableType);

        return $return;
    }

    /**
     * Create a new ResultVariable
     *
     * @param $identifier
     * @param $type
     * @param $value
     * @param string $variableType
     */
    public function __construct($identifier, $type, $value, $variableType = self::OUTCOME_VARIABLE)
    {
        $this->identifier = $identifier;
        $this->type = $type;
        $this->value = $value;
        $this->setVariableType($variableType);
    }

    /**
     * Returns the variable identifier
     *
     * @return string
     */
    public function getId()
    {
        return $this->identifier;
    }

    /**
     * Returns the variable value(s)
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Return the variable type
     *
     * @return mixed
     */
    public function getVariableType()
    {
        return $this->variableType;
    }

    /**
     * Get the type of the current variable
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * (non-PHPdoc)
     * @see JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize()
    {
        $values = is_array($this->value) ? $this->value : [$this->value];
        $valueArray = [];
        foreach ($values as $val) {
            $valueArray[] = [
                "baseType" => $this->type,
                "valueString" => $val
            ];
        }
        return [
            'identifier' => $this->identifier,
            'values' => $valueArray
        ];
    }

    /**
     * Set the variable type
     *
     * It must be an allowedType, otherwise it will be an outcome variable
     *
     * @param $type
     */
    protected function setVariableType($type)
    {
        if (!in_array($type, $this->getAllowedVariableTypes())) {
            $type = self::OUTCOME_VARIABLE;
        }
        $this->variableType = $type;
    }

    /**
     * Get the allowed types for a variable
     *
     * - outcome
     * - trace
     * - response
     * - template
     *
     * @return array
     */
    protected function getAllowedVariableTypes()
    {
        return [
            self::OUTCOME_VARIABLE,
            self::TRACE_VARIABLE,
            self::RESPONSE_VARIABLE,
            self::TEMPLATE_VARIABLE,
        ];
    }
}
