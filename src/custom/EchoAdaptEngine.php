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

use GuzzleHttp\Psr7\Request;
use function GuzzleHttp\Psr7\stream_for;
use oat\libCat\CatEngine;
use Psr\Http\Message\RequestInterface;
use oat\prePsr\httpMiddlewares\MiddlewareInterface;

/**
 * Implementation of the EchoAdapt engine
 *
 * @author Joel Bout, <joel@taotesting.com>
 */
class EchoAdaptEngine implements CatEngine
{
    const OPTION_VERSION = 'version';
    const OPTION_CONNECTOR = 'connector';

    /** @var string The base url of EchoAdaptEngine */
    protected $endpoint;

    /** @var MiddlewareInterface The connector to handle the request */
    protected $connector;

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
        $this->createEndpoint($args);
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
     * Helper to facilitate calls to the server. Wrap the call to current connector.
     * Send the request to the server and return the decoded content.
     *
     * @param string $url
     * @param string $method
     * @param array $data
     * @return array
     */
    public function call($url, $method = 'GET', $data = [])
    {
        $request = $this->getRequest($this->buildUrl($url), $method, $data);
        $response = $this->getEchoAdaptConnector()->process($request, null);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get a request, add $params to request body and return it
     *
     * @param $url
     * @param string $method
     * @param array $params
     * @return RequestInterface
     */
    protected function getRequest($url, $method = 'GET', array $params = array())
    {
        $request = new Request($method, $url);
        if (!empty($params)) {
            $body = stream_for(json_encode($params));
            $request = $request->withBody($body)->withAddedHeader('Content-Type', 'application/json');
        }
        return $request;
    }

    /**
     * Get the current echoAdapt connector.
     *
     * @return MiddlewareInterface
     */
    protected function getEchoAdaptConnector()
    {
        return $this->connector;
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
     * Create the connector and version, based on the entry $options.
     *
     * @param array $options
     * @throws \common_exception_InconsistentData
     */
    protected function createEndpoint(array $options = [])
    {
        if (isset($options[self::OPTION_VERSION])) {
            $this->version = $options[self::OPTION_VERSION];
        } else {
            throw new \InvalidArgumentException('No API version provided. Cannot connect to endpoint.');
        }

        if (!isset($options[self::OPTION_CONNECTOR])) {
            throw new \InvalidArgumentException('No API connector provided. Cannot connect to endpoint.');
        }

        $connector = $options[self::OPTION_CONNECTOR];
        if (is_array($connector)) {
            $connectorClass = isset($connector['class']) ? $connector['class'] : null;
            $connectorOptions = isset($connector['options']) ? $connector['options'] : array();
            if (!is_a($connectorClass, MiddlewareInterface::class, true)) {
                throw new \InvalidArgumentException('Connector has to implement middleware interface.');
            }
            $connector = new $connectorClass($connectorOptions);
        } elseif (is_object($connector)) {
            if (!is_a($connector, MiddlewareInterface::class)) {
                throw new \InvalidArgumentException('Connector has to implement middleware interface.');
            }
        } else {
            throw new \InvalidArgumentException('Connector is misconfigured.');
        }
        $this->connector = $connector;
    }
}