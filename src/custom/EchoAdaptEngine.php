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

use oat\libCat\CatEngine;
use GuzzleHttp\Psr7\Request;
use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;
use GuzzleHttp\Psr7\Stream;
use function GuzzleHttp\Psr7\stream_for;
use GuzzleHttp\Client;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
/**
 * Interface to describe the interaction between the testrunner and the adaptive engine
 *
 * @author Joel Bout, <joel@taotesting.com>
 */
class EchoAdaptEngine implements CatEngine
{
    const ENGINE_VERSION = 'v1';
    
    private $endpoint;
    
    /**
     * Setup the EchoAdaptEngine
     *
     * @param string $endpoint URL of the service
     */
    public function __construct($endpoint) {
        $this->endpoint = rtrim($endpoint, '/');
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
     * Helper to facilitate calls to the server
     * 
     * @param string $url
     * @param string $method
     * @param array $data
     * @return array
     */
    public function call($url, $method = 'GET', $data = [])
    {
        $request = new Request($method, $this->endpoint.'/'.self::ENGINE_VERSION.'/'.$url);
        if (!empty($data)) {
            $body = stream_for(json_encode($data));
            $request = $request->withBody($body)->withAddedHeader('Content-Type', 'application/json');
        }
        
        \common_Logger::d('Call to '.$request->getUri());
        $response = $this->send($request);
        return $response;
    }
    
    /**
     * Function that handles communication and timeouts
     *
     * @param RequestInterface $request
     * @return mixed
     */
    private function send(RequestInterface $request) {
    
//        try {
            $response = $this->getClient()->send($request);
//        } catch(RequestException $e) {
//            $response = $e->getResponse();
//        }
    
        return json_decode($response->getBody()->getContents(), true );
    }
    
    /**
     * @return \GuzzleHttp\Client
     */
    private function getClient() {
        return new Client();
    }
}
