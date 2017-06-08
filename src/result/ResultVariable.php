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
    private $identifier;
    
    private $type;
    
    private $value;
    
    /**
     * Create a Resultvariable from the json array
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
        return new static($array["identifier"], $value['baseType'], $values);
    }
    
    public function __construct($identifier, $type, $value)
    {
        $this->identifier = $identifier;
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * Returns the variable identifier
     * @return string
     */
    public function getId()
    {
        return $this->identifier;
    }

    /**
     * Returns the variable value(s)
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
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
}
