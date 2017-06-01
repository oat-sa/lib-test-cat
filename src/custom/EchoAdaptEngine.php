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
 * @author Joel Bout, <joel.bout@tudor.lu>
 */
class EchoAdaptEngine implements CatEngine
{
    const ENGINE_VERSION = 'v1';
    
    private $endpoint;
    
    public function __construct($endpoint) {
        $this->endpoint = rtrim($endpoint, '/');
    }
    
    
    public function setupSection($configuration, $qtiUsageData = null, $qtiMetaData = null)
    {
        return new EchoAdaptSection($this, $configuration);
    }
    
    public function restoreSection($jsonString)
    {
        $identifier = json_decode($jsonString);
        if (!is_numeric($identifier)) {
            throw new \Exception('Unable to restore EchoAdaptSection');
        }
        return new EchoAdaptSection($this, $identifier);
    }
    
    /**
     * 
     * @param string $url
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
    
    private function send(RequestInterface $request) {
    
        try {
            $response = $this->getClient()->send($request);
        } catch(RequestException $e) {
            $response = $e->getResponse();
    
            $iteration++;
            $canReplay = null;
            if ($response instanceof ResponseInterface) {
                $this->triggerCommunicationEvent(FailedRequestEvent::class, $request, $response );
                $canReplay = $this->canReplay($response, $request, $iteration);
            } else {
                \common_Logger::e('Incorrect response from ' . (string)$request->getUri());
            }
            return $canReplay;
        }
    
        return json_decode($response->getBody()->getContents(), true );
    }
    
    private function getClient() {
        return new Client();
    }
}
