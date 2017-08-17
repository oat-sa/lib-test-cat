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
use oat\oatbox\service\ServiceManager;
use oat\taoOauth\model\connector\Connector;
use oat\taoOauth\model\connector\ConnectorFactory;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Implementation of the EchoAdapt engine
 *
 * @author Joel Bout, <joel@taotesting.com>
 */
class EchoAdaptEngine implements CatEngine, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    const ENGINE_VERSION_1 = 'v1';
    const ENGINE_VERSION_1_1 = 'v1.1';

    const OPTION_CONNECTOR_VERSION = 'version';

    /** @var string The base url of EchoAdaptEngine */
    protected $endpoint;

    /** @var Connector */
    protected $connector;

    protected $currentVersion;

    /**
     * Setup the EchoAdaptEngine
     *
     * @param string $endpoint URL of the service
     * @param array $args
     */
    public function __construct($endpoint, $args = array())
    {
        $this->endpoint = rtrim($endpoint, '/');
        $this->currentVersion = $this->getValidVersion($args);
        $this->connector = $this->buildConnector($args);
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
     * 
     * @param string $url
     * @param string $method
     * @param array $data
     * @return array
     */
    public function call($url, $method = \Request::HTTP_GET, $data = [])
    {
        return $this->getEchoAdaptConnector()->request($this->buildUrl($url), $data, $method);
    }

    /**
     * Get ServiceLocator
     *
     * @return ServiceManager|\Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        if (!is_null($this->serviceLocator)) {
            return $this->serviceLocator;
        }
        return ServiceManager::getServiceManager();
    }

    /**
     * Get the current echoAdapt connector.
     *
     * @return Connector
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
        return $this->endpoint . '/' . $this->getCurrentVersion() . '/' . $url;
    }

    /**
     * Extract the version from $args
     * - If a version has been provided, validate and return it
     * - If no version has been provided, set version v1.1 by default
     *
     * @param array $args
     * @return mixed|string
     * @throws \common_exception_InconsistentData In case of wrong version
     */
    protected function getValidVersion(array $args)
    {
        if (isset($args[self::OPTION_CONNECTOR_VERSION])) {
            $this->validateVersion($args[self::OPTION_CONNECTOR_VERSION]);
            return $args[self::OPTION_CONNECTOR_VERSION];
        } else {
            return self::ENGINE_VERSION_1_1;
        }
    }

    /**
     * Validate a given $version by checking if it's an allowed version
     *
     * @param $version
     * @throws \common_exception_InconsistentData In case of wrong version
     */
    protected function validateVersion($version)
    {
        $allowedVersions = [self::ENGINE_VERSION_1, self::ENGINE_VERSION_1_1];
        if (is_string($version) && in_array($version, $allowedVersions)) {
            return;
        }
        throw new \common_exception_InconsistentData('EchoAdapt API version provided is not valid, expected: ' . implode(', ', $allowedVersions));
    }

    /**
     * Get the api version used to connect to echo adapt
     * - v1 : no auth
     * - v1.1 : oauth
     *
     * @return string
     */
    protected function getCurrentVersion()
    {
        return $this->currentVersion;
    }

    /**
     * Build the connector based on the used echoAdapt version.
     *
     * @param array $options
     * @return mixed
     * @throws \common_exception_NotImplemented
     * @throws \common_exception_PreConditionFailure
     */
    protected function buildConnector(array $options = [])
    {
        /** @var \common_ext_ExtensionsManager $extensionManager */
        $extensionManager = $this->getServiceLocator()->get(\common_ext_ExtensionsManager::SERVICE_ID);
        if (!$extensionManager->isEnabled('taoOauth')) {
            throw new \common_exception_PreConditionFailure('Echo adapt engine requires taoOauth extension to connect to API');
        }

        $connectorFactory = new ConnectorFactory();

        if ($this->getCurrentVersion() == self::ENGINE_VERSION_1) {
            return $connectorFactory->buildNoAuthConnector($options);
        }

        if ($this->getCurrentVersion() == self::ENGINE_VERSION_1_1) {
            return $connectorFactory->buildOauthConnector($options);
        }

        throw new \common_exception_NotImplemented(
            'EchoAdapt API version "' . $this->getCurrentVersion() . '" does not have associated connector.'
        );
    }
}