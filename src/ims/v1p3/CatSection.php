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

use oat\libCat\AbstractCatSection;
use oat\libCat\CatEngine as CatEngineInterface;
use oat\libCat\exception\CatEngineException;
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
     *
     * @param CatEngineInterface $engine
     * @param                    $settings
     * @param string             $qtiUsageData
     * @param string             $qtiMetaData
     * @param string             $sectionId
     *
     * @throws CatEngineException
     */
    public function __construct(
        CatEngineInterface $engine,
        $settings,
        $qtiUsageData = null,
        $qtiMetaData = null,
        $sectionId = null
    ) {
        parent::__construct($engine, $settings, $qtiUsageData, $qtiMetaData);

        $this->sectionId = is_null($sectionId)
            ? $this->requestSectionIdentifier($settings, $qtiMetaData, $qtiUsageData)
            : $sectionId;
    }

    /**
     * @param        $settings
     * @param string $qtiMetaData
     * @param string $qtiUsageData
     *
     * @return string
     *
     * @throws CatEngineException
     */
    private function requestSectionIdentifier($settings, $qtiMetaData, $qtiUsageData)
    {
        $result = $this->engine->call(
            'sections',
            'POST',
            json_encode(
                [
                    "qtiMetadata"          => base64_encode($qtiMetaData),
                    "qtiUsagedata"         => base64_encode($qtiUsageData),
                    "sectionConfiguration" => base64_encode($settings)
                ]
            )
        );

        if (empty($result['sectionIdentifier'])) {
            throw new CatEngineException('Unable create CatSection. Section identifier is not defined');
        }

        return $result['sectionIdentifier'];
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return [
            'adaptiveSettingsRef' => $this->settings,
            'qtiUsagedataRef' => $this->qtiUsageData,
            'qtiMetadataRef' => $this->qtiMetaData,
            'sectionId' => $this->sectionId,
        ];
    }

    /**
     * @return string
     */
    public function getSectionId()
    {
        return $this->sectionId;
    }

    /**
     * @param array $configurationData
     * @param array $context
     * @return CatSession
     */
    public function initSession($configurationData = [], $context = [])
    {
        /** TODO: probably this is ACT specific */
        $configurationData['priorData'][] = $this->formatKVdata(["initialEstimatedAbility" => '0.0']);

        $data = $this->engine->call(
            $this->getInitUrl(),
            'POST',
            ResultFormatter::format($configurationData)
        );

        return $this->createSession($data, new SessionContext($context));
    }

    /**
     * @param string $jsonString
     * @return CatSession
     */
    public function restoreSession($jsonString)
    {
        $data = json_decode($jsonString, true);
        $context = isset($data['context']) ? new SessionContext($data['context']) : null;
        return $this->createSession($data['data'], $context);
    }

    /**
     * @param array $data
     * @param SessionContext $context
     * @return \oat\libCat\CatSession|CatSession
     */
    protected function createSession(array $data, SessionContext $context = null)
    {
        return new CatSession(
            $this->engine,
            $this->getSectionId(),
            $data,
            $context
        );
    }

    /**
     * @return string
     */
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
