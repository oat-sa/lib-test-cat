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

/**
 * Implementation of the EchoAdapt engine
 *
 * @author Joel Bout, <joel@taotesting.com>
 */
class EchoAdaptEngine implements CatEngine
{
    const OPTION_VERSION = 'version';
    const OPTION_CLIENT = 'client';

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
     * @param array $args
     */
    public function __construct($endpoint, $args = array())
    {
        $this->endpoint = rtrim($endpoint, '/');
        $this->createClient($args);
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
            throw new \Exception('Unable to restore EchoAdaptSection');
        }
        return new EchoAdaptSection($this, $identifier);
    }

    /**
     * Helper to facilitate calls to the server. Wrap the call to EchoAdapt client.
     * Send the request to the server and return the decoded content.
     *
     * @param string $url
     * @param string $method
     * @param array $data
     * @return array
     */
    public function call($url, $method = 'GET', $data = [])
    {
        $response = $this->getEchoAdaptClient()->request($method, $this->buildUrl($url), $data);
        return json_decode($response->getBody()->getContents(), true);
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

    /**
     * Create the client and version, based on the entry $options.
     *
     * @param array $options
     * @throws \common_exception_InconsistentData
     */
    protected function createClient(array $options = [])
    {
        if (isset($options[self::OPTION_VERSION])) {
            $this->version = $options[self::OPTION_VERSION];
        } else {
            throw new \InvalidArgumentException('No API version provided. Cannot connect to endpoint.');
        }

        if (!isset($options[self::OPTION_CLIENT])) {
            throw new \InvalidArgumentException('No API client provided. Cannot connect to endpoint.');
        }

        $client = $options[self::OPTION_CLIENT];
        if (is_array($client)) {
            $clientClass = isset($client['class']) ? $client['class'] : null;
            $clientOptions = isset($client['options']) ? $client['options'] : array();
            if (!is_a($clientClass, ClientInterface::class, true)) {
                throw new \InvalidArgumentException('Client has to implement ClientInterface interface.');
            }
            $client = new $clientClass($clientOptions);
        } elseif (is_object($client)) {
            if (!is_a($client, ClientInterface::class)) {
                throw new \InvalidArgumentException('Client has to implement ClientInterface interface.');
            }
        } else {
            throw new \InvalidArgumentException('Client is misconfigured.');
        }
        $this->client = $client;
    }
}