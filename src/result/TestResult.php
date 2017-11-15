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
 * Class TestResult
 *
 * Data object that regroups all result variables (response, outcome, template)
 *
 * @package oat\libCat\result
 */
class TestResult implements \JsonSerializable
{
    /** @var ResultVariable[] */
    protected $variables;

    /**
     * TestResult constructor.
     *
     * @param $variables
     */
    public function __construct($variables)
    {
        $this->variables = is_array($variables) ? $variables : [$variables];
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
     * (non-PHPdoc)
     * @see JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize()
    {        \common_Logger::w(__METHOD__);

        return array(
            'outcomeVariables' => $this->getVariables(),
        );
        $variables = [];
        foreach ($this->getVariables() as $variable) {
            switch ($variable->getVariableType()) {
                case ResultVariable::TEMPLATE_VARIABLE:
                    $variables['templateVariables'][] = $variable;
                    break;
                case ResultVariable::RESPONSE_VARIABLE:
                    $variables['responseVariables'][] = $variable;
                    break;
                case ResultVariable::TRACE_VARIABLE:
                    $variables['traceVariables'][] = $variable;
                    break;
                case ResultVariable::OUTCOME_VARIABLE:
                    $variables['outcomeVariables'][] = $variable;
                    break;
            }
        }
        return $variables;
    }
}