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

namespace oat\libCat\custom;

use oat\libCat\CatSession;
use oat\libCat\result\ResultVariable;
/**
 * Interface to describe the interaction between the testrunner and the adaptive engine
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 */
class EchoAdaptSession implements CatSession
{
    private $engine;
    
    private $sectionId;
    
    private $testTakerSessionId;
    
    private $nextItems;
    
    private $numberOfItemsInNextStage;
    
    private $linear;
    
    private $assesmentResult;
    
    private $sessionState;
    
    public function __construct($engine, $sectionId, $testTakerSessionId, $nextItems, $numberOfItemsInNextStage, $linear, $assesmentResult, $sessionState)
    {
        $this->engine = $engine;
        $this->sectionId = $sectionId;
        $this->testTakerSessionId = $testTakerSessionId;
        $this->nextItems = $nextItems;
        $this->numberOfItemsInNextStage = $numberOfItemsInNextStage;
        $this->linear = $linear;
        $this->assesmentResult = $assesmentResult;
        $this->sessionState = $sessionState;
    }
    
    public function getTestMap($results = [])
    {
        if (!empty($results)) {
            $data = $this->engine->call(
                'tests/'.$this->sectionId.'/test_taker_sessions/'.$this->testTakerSessionId.'/results',
                'POST',
                ["results" => $results,"sessionState" => $this->sessionState]
            );
            $this->nextItems = $data['nextItems'];
            $this->numberOfItemsInNextStage = $data['numberOfItemsInNextStage'];
            $this->linear = $data['linear'];
            $this->assesmentResult = $data['assesmentResult'];
            $this->sessionState = $data['sessionState'];
        }
        return $this->nextItems;
    }
    
    /**
     * Returns testresults provided by the engine
    */
    public function getResults()
    {
        $variables = [];
        $variableTypes = ["templateVariables", "responseVariables", "outcomeVariables"];
        foreach ($variableTypes as $varType) {
            foreach ($this->assesmentResult['testResult'][$varType] as $variable) {
                $variables[] = ResultVariable::restore($variable);
            }
        }
        return $variables;
    }
    
    public function jsonSerialize()
    {
        return [
            'testTakerSessionId' => $this->testTakerSessionId,
            'nextItems' => $this->nextItems,
            'numberOfItemsInNextStage' => $this->numberOfItemsInNextStage,
            'linear' => $this->linear,
            'assesmentResult' => $this->assesmentResult,
            'sessionState' => $this->sessionState
        ];
    }
}
