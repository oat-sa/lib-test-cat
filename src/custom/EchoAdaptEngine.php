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

use GuzzleHttp\ClientInterface;
use oat\libCat\CatEngine;
use oat\libCat\exception\CatEngineConnectivityException;
use oat\libCat\exception\CatEngineException;
use oat\libCat\AbstractCatEngine;

/**
 * Implementation of the EchoAdapt engine
 *
 * @author Joel Bout, <joel@taotesting.com>
 */
class EchoAdaptEngine extends AbstractCatEngine
{

    /**
     * @inheritdoc
     */
    public function call($url, $method = 'GET', $data = null)
    {
        return parent::call($url, $method, $data);
    }

    /**
     * @inheritdoc
     */
    protected function createSection($adaptiveSettingsRef, $qtiUsageData = null, $qtiMetaData = null)
    {
        return new EchoAdaptSection($this, $adaptiveSettingsRef);
    }

    /**
     * (non-PHPdoc)
     * @see \oat\libCat\CatEngine::restoreSection()
     */
    public function restoreSection($jsonString)
    {
        $identifier = json_decode($jsonString);
        if (!is_numeric($identifier)) {
            throw new CatEngineException('Unable to restore EchoAdaptSection');
        }
        return new EchoAdaptSection($this, $identifier);
    }

}
