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

use oat\libCat\CatSection;
use function GuzzleHttp\json_decode;

/**
 * Implementation of an EchoAdapt section
 *
 * @author Joel Bout, <joel@taotesting.com>
 */
class EchoAdaptSection implements CatSection
{
    /**
     * @var EchoAdaptEngine
     */
    private $engine;
    
    private $sectionId;
    
    public function __construct($engine, $sectionId) {
        $this->engine = $engine;
        $this->sectionId = $sectionId;
    }
    
    /**
     * 
     * @param string $priorData
     * @return \oat\libCat\custom\EchoAdaptSession
     */
    public function initSession($priorData = null)
    {
        $data = $this->engine->call(
            'tests/'.$this->sectionId.'/test_taker_sessions',
            'POST',
            EchoAdaptFormatter::format(["initialEstimatedAbility" => ['0.0']])
        );

        $data = EchoAdaptFormatter::parse($data);

        return new EchoAdaptSession(
            $this->engine
            ,$this->sectionId
            ,$data['testTakerSessionId']
            ,$data['nextItems']
            ,$data['numberOfItemsInNextStage']
            ,$data['linear']
            ,$data['assesmentResult']
            ,$data['sessionState']
        );
    }
    

    public function restoreSession($jsonString)
    {
        $data = json_decode($jsonString, true);
        $session = new EchoAdaptSession(
            $this->engine
            ,$this->sectionId
            ,$data['testTakerSessionId']
            ,$data['nextItems']
            ,$data['numberOfItemsInNextStage']
            ,$data['linear']
            ,$data['assesmentResult']
            ,$data['sessionState']
        );
        return $session;
    }
    
    public function getItemReferences() {
        $response = $this->engine->call('tests/'.$this->sectionId.'/items');
        return json_decode($response, true);
    }
    
    public function jsonSerialize()
    {
        return $this->sectionId;
    }
    
    public function getSectionId()
    {
        return $this->sectionId;
    }
}
