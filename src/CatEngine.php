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

namespace oat\libCat;

use Psr\Http\Message\StreamInterface;
/**
 * Interface to describe the interaction between the testrunner and the adaptive engine
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 */
interface CatEngine
{
    /**
     * Setups an engine using
     *   adaptiveEngineRef
     *   adaptiveConfigurationRef
     *   qtiUsagedataRef
     *   qtiMetadataRef
     *
     * @param StreamInterface $configuration
     * @param StreamInterface $qtiUsageData
     * @param StreamInterface $qtiMetaData
     * @return CatSection
     */
    public function setupSection($configuration, $qtiUsageData = null, $qtiMetaData = null);
    
    /**
     * Restores a section from the jsonString
     * 
     * @param string $jsonString
     * @return CatSection
     */
    public function restoreSection($jsonString);
}
