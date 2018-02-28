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

namespace oat\libCat;

use oat\libCat\CatSection;
use function GuzzleHttp\json_decode;

/**
 * Class AbstractSection
 * @package oat\libCat
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
abstract class AbstractCatSection implements CatSection
{
    /**
     * @var CatEngine
     */
    protected $engine;

    protected $settings;

    protected $qtiUsageData;

    protected $qtiMetaData;

    /**
     * AbstractCatSection constructor.
     * @param CatEngine $engine
     * @param $settings
     * @param $qtiUsageData
     * @param $qtiMetaData
     */
    public function __construct(CatEngine $engine, $settings, $qtiUsageData = null, $qtiMetaData = null) {
        $this->engine = $engine;
        $this->settings = $settings;
        $this->qtiUsageData = $qtiUsageData;
        $this->qtiMetaData = $qtiMetaData;
    }
    
    /**
     * @param string $configurationData
     * @return CatSession
     */
    public function initSession($configurationData = [], $context = [])
    {
        $data = $this->engine->call(
            $this->getInitUrl(),
            'POST',
            $configurationData
        );

        return $this->createSession($data);
    }

    /**
     * @param string $jsonString
     * @return CatSession
     */
    public function restoreSession($jsonString)
    {
        $data = json_decode($jsonString, true);
        return $this->createSession($data);
    }

    /**
     * @return mixed
     */
    public function getQtiUsageData()
    {
        return $this->qtiUsageData;
    }

    /**
     * @return mixed
     */
    public function getQtiMetaData()
    {
        return $this->qtiMetaData;
    }

    /**
     * @return string
     */
    abstract public function getSectionId();

    /**
     * @return mixed
     */
    abstract public function jsonSerialize();

    /**
     * Factory method to create CatSession instance
     *
     * @param array $data
     * @return CatSession
     */
    abstract protected function createSession(array $data);

    /**
     * @return string
     */
    abstract protected function getInitUrl();

}
