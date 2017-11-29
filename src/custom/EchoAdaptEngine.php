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

/**
 * Implementation of the EchoAdapt engine
 *
 * @author Joel Bout, <joel@taotesting.com>
 */
class EchoAdaptEngine implements CatEngine
{
    /** @var string The base url of EchoAdaptEngine */
    protected $endpoint;

    /** @var ClientInterface The client to handle the request */
    protected $client;

    /** @var  string The API version to reach */
    protected $version;

    /**
     * Setup the EchoAdaptEngine
     *
     * @param string $endpoint URL of the service
     * @param $version
     * @param ClientInterface $client
     */
    public function __construct($endpoint, $version, ClientInterface $client)
    {
        $this->endpoint = rtrim($endpoint, '/');
        $this->version = $version;
        $this->client = $client;
    }

    /**
     * (non-PHPdoc)
     * @see \oat\libCat\CatEngine::setupSection()
     */
    public function setupSection($configuration, $qtiUsageData = null, $qtiMetaData = null)
    {
        return new EchoAdaptSection($this, $configuration);
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

    /**
     * Helper to facilitate calls to the server. Wrap the call to EchoAdapt client.
     * Send the request to the server and return the decoded content.
     *
     * @param $url
     * @param string $method
     * @param null $data
     * @return string
     * @throws CatEngineConnectivityException
     */
    public function call($url, $method = 'GET', $data = null)
    {
        try {
            $options = ['headers' => []];
            if ($data != null) {
                if (!is_string($data)) {
                    throw new CatEngineException('The request body has to a string to request the url ' . $this->buildUrl($url));
                }
                $options['body'] = $data;
            }

            $response = $this->getEchoAdaptClient()->request($method, $this->buildUrl($url), $options);

            if ($response->getStatusCode() != 200) {
                throw new CatEngineException(
                    'The CAT engine server cannot handle the request to ' . $this->buildUrl($url) .
                    isset($options['body']) ? ' with data (' . $options['body'] . ')' : ' (No body data)'
                );
            }

            return $response->getBody()->getContents();
        } catch (\Exception $e) {
           throw new CatEngineConnectivityException('', 0, $e);
        }
    }

    /**
     * Get the EchoAdapt client.
     *
     * @return ClientInterface
     */
    protected function getEchoAdaptClient()
    {
        return $this->client;
    }

    /**
     * Build the full url associated to relative $url
     *
     * @param $url
     * @return string
     */
    protected function buildUrl($url)
    {
        return $this->endpoint . '/' . $this->getVersion() . '/' . $url;
    }

    /**
     * Get the api version used to connect to echo adapt
     *
     * @return string
     */
    protected function getVersion()
    {
        return $this->version;
    }
}
