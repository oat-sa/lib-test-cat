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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA
 */

namespace oat\libCat\ims\v1p3;

use oat\libCat\CatEngine;
use oat\libCat\CatSection as CatSectionInterface;
use oat\libCat\AbstractCatSection;
use function GuzzleHttp\json_decode;

/**
 * Implementation of an CAT
 */
class CatSection extends AbstractCatSection
{
    /** @var string */
    protected $sectionId;

    /**
     * CatSection constructor.
     * @param CatEngine $engine
     * @param $settings
     * @param string $qtiUsageData
     * @param string $qtiMetaData
     * @param string $sectionId
     */
    public function __construct(CatEngine $engine, $settings, $qtiUsageData = null, $qtiMetaData = null, $sectionId = null)
    {
        parent::__construct($engine, $settings, $qtiUsageData, $qtiMetaData);
        $this->sectionId = $sectionId;
        if ($this->sectionId === null) {
            $result = $this->engine->call(
                'sections',
                'POST',
                json_encode([
                    "qtiMetadata" => base64_encode($qtiMetaData),
                    "qtiUsagedata" => base64_encode($qtiUsageData),
                    "sectionConfiguration" => base64_encode($settings)
                ])
            );
            if (!isset($result['sectionIdentifier']) || !is_numeric($result['sectionIdentifier'])) {
                throw new CatEngineException('Unable create CatSection');
            }
            $this->sectionId = $result['sectionIdentifier'];
        }
    }

    public function jsonSerialize()
    {
        return [
            'adaptiveSettingsRef' => $this->settings,
            'qtiUsagedataRef' => $this->qtiUsageData,
            'qtiMetadataRef' => $this->qtiMetaData,
            'sectionId' => $this->sectionId,
        ];
    }

    public function getSectionId()
    {
        return $this->settings;
    }

    public function initSession($configurationData = [])
    {
        /** TODO: probably this is ACT specific */
        $configurationData['priorData'][] = $this->formatKVdata(["initialEstimatedAbility" => '0.0']);
        return parent::initSession(ResultFormatter::format($configurationData));
    }

    protected function createSession(array $data)
    {
        return new CatSession(
            $this->engine,
            $this->getSectionId(),
            $data
        );
    }

    protected function getInitUrl()
    {
        return 'sections/'.$this->getSectionId().'/sessions';
    }

    /**
     * @param array $data
     * @return array
     */
    protected function formatKVdata(array $data)
    {
        $result = [];
        foreach ($data as $key => $value) {
            $result['key'] = $key;
            $result['value'] = $value;
        }
        return $result;
    }
}
