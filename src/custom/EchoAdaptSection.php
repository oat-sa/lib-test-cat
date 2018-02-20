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
use oat\libCat\AbstractCatSection;
use function GuzzleHttp\json_decode;

/**
 * Implementation of an EchoAdapt section
 *
 * @author Joel Bout, <joel@taotesting.com>
 */
class EchoAdaptSection extends AbstractCatSection
{

    /**
     * Note: this is not part of IMS CAT API standard
     * @return mixed
     */
    public function getItemReferences()
    {
        return $this->engine->call('tests/'.$this->getSectionId().'/items');
    }
    
    public function jsonSerialize()
    {
        return $this->settings;
    }
    
    public function getSectionId()
    {
        return $this->settings;
    }

    public function initSession($configurationData = [], $context = [])
    {
        return parent::initSession(EchoAdaptFormatter::format(["initialEstimatedAbility" => ['0.0']]));
    }

    protected function createSession(array $data)
    {
        return new EchoAdaptSession(
            $this->engine,
            $this->getSectionId(),
            $data
        );
    }

    protected function getInitUrl()
    {
        return 'tests/'.$this->getSectionId().'/test_taker_sessions';
    }
}
