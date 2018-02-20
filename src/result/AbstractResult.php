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


abstract class AbstractResult implements \JsonSerializable
{
    /** @var ResultVariable[] */
    protected $variables;

    /**
     * AbstractResult constructor.
     *
     * @param $variables
     */
    public function __construct($variables, $timestamp = null)
    {
        $this->variables = is_array($variables) ? $variables : [$variables];
        $this->timestamp = $timestamp;
    }

    /**
     * Returns the variables of the item
     *
     * @return ResultVariable[]
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * The date stamp of when this result was recorded.
     *
     * @return numeric
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * (non-PHPdoc)
     * @see JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize()
    {
        return [
            'variables' => $this->getVariables(),
            'timestamp' => $this->getTimestamp(),
        ];
    }

}