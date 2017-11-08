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

use oat\libCat\result\variable\OutcomeVariable;
use oat\libCat\result\variable\ResponseVariable;
use oat\libCat\result\variable\TemplateVariable;
use oat\libCat\result\variable\TraceVariable;

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

    public function __construct($variables)
    {
        $this->variables = is_array($variables) ? $variables : [$variables];
    }

    /**
     * Returns the variables of the item
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
    {
        $outcomeVariables = $responseVariables = $templateVariables = $traceVariables = [];
        foreach ($this->getVariables() as $variable) {
            switch (get_class($variable)) {
                case TraceVariable::class:
                    $traceVariables[] = $variable;
                    break;
                case ResponseVariable::class:
                    $responseVariables[] = $variable;
                    break;
                case TemplateVariable::class:
                    $templateVariables[] = $variable;
                    break;
                case OutcomeVariable::class:
                default:
                    $outcomeVariables[] = $variable;
                    break;
            }
        }

        $array = [];

        if (!empty($traceVariables)) {
            $array['traceVariables'] = $traceVariables;
        }
        if (!empty($responseVariables)) {
            $array['responseVariables'] = $responseVariables;
        }
        if (!empty($templateVariables)) {
            $array['templateVariables'] = $templateVariables;
        }
        if (!empty($outcomeVariables)) {
            $array['outcomeVariables'] = $outcomeVariables;
        }

        return $array;
    }
}