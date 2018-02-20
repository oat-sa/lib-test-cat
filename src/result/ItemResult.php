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
 * Data object that regroups all result variables (response, outcome, template)
 * for a given item
 *
 * @author Joel Bout, <joel@taotesting.com>
 */
class ItemResult extends AbstractResult
{
    /**
     * @var string The associated item id
     */
    private $itemRefId;

    /**
     * Unserialize an itemResult formatted as array
     *
     * @param array $data
     * @return ItemResult
     */
    static public function restore(array $data)
    {
        $itemIdentifier = $data['identifier'];
        $variables = [];
        $itemVariables = isset($data['outcomeVariables']) ? $data['outcomeVariables'] : $data['variables'];
        foreach ($itemVariables as $variable) {
            if (empty($variable)) {
                continue;
            }
            $variables[] = ResultVariable::restore($variable);
        }
        return new self($itemIdentifier, $variables, $data['timestamp']);
    }

    /**
     * ItemResult constructor.
     *
     * @param $itemRefId
     * @param $variables
     * @param $timestamp
     */
    public function __construct($itemRefId, $variables, $timestamp = null)
    {
        parent::__construct($variables, $timestamp);
        $this->itemRefId = $itemRefId;
    }

    /**
     * Returns the item reference id
     *
     * @return string
     */
    public function getItemRefId()
    {
        return $this->itemRefId;
    }

    /**
     * (non-PHPdoc)
     * @see JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize()
    {
        return array_merge(['identifier' => $this->itemRefId], parent::jsonSerialize());
    }

}
