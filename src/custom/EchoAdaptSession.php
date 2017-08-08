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
use oat\libCat\result\ItemResult;
/**
 * Implementation of the Echoadapt session
 *
 * @author Joel Bout, <joel@taotesting.com>
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
    
    /**
     * (non-PHPdoc)
     * @see \oat\libCat\CatSession::getTestMap()
     */
    public function getTestMap($results = [])
    {
        if (!empty($results)) {
            $data = $this->engine->call(
                'tests/'.$this->sectionId.'/test_taker_sessions/'.$this->testTakerSessionId.'/results',
                'POST',
                ["results" => $this->filterResults($results),"sessionState" => $this->sessionState]
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
     * (non-PHPdoc)
     * @see \oat\libCat\CatSession::getResults()
     */
    public function getResults()
    {
        $variables = [];
        $variableTypes = ["templateVariables", "responseVariables", "outcomeVariables"];
        foreach ($variableTypes as $varType) {
            if (isset($this->assesmentResult['testResult'][$varType])) {
                foreach ($this->assesmentResult['testResult'][$varType] as $variable) {
                    $variables[] = ResultVariable::restore($variable);
                }
            }
        }
        return $variables;
    }
    
    /**
     * (non-PHPdoc)
     * @see JsonSerializable::jsonSerialize()
     */
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
    
    /**
     * Get Section Identifier.
     * 
     * Returns the identifier of section the Echo Adapt session is related to.
     * 
     * @return string
     */
    public function getSectionId()
    {
        return $this->sectionId;
    }
    
    /**
     * Get Test Taker Session Identifier.
     * 
     * Returns the unique identifier representing both the Echo Adapt session and the test taker taking the session.
     * 
     * @return string
     */
    public function getTestTakerSessionId()
    {
        return $this->testTakerSessionId;
    }

    /**
     * filter out non 'score' variables
     *
     * @param ItemResult[] $results
     * @return ItemResult[]
     */
    private function filterResults($results)
    {
        foreach ($results as $key => $result) {
            $scoreOnly = [];
            foreach ($result->getVariables() as $variable) {
                if (strtolower($variable->getId()) == 'score') {
                    $scoreOnly[] = $variable;
                }
            }
            $results[$key] = new ItemResult($result->getItemRefId(), $scoreOnly);
        }
        return $results;
    }
}
