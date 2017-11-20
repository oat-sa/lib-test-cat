<?php
/**
 * Created by PhpStorm.
 * User: siwane
 * Date: 20/11/17
 * Time: 14:28
 */

namespace oat\libCat\result;


abstract class AbstractResult implements \JsonSerializable
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
    {
//        \common_Logger::i(print_r($this->getVariables(),true));
//        return $this->getVariables();
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