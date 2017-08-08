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

/**
 * Interface to describe an adaptive section within a test
 *
 * @author Joel Bout, <joel@taotesting.com>
 */
interface CatSection extends \JsonSerializable
{
    /**
     * Initialize a session for an adaptive section.
     * The priorData data format is implementation
     * specific and not covered by the standard.
     * 
     * @param mixed $priorData
     * @return CatSession
     */
    public function initSession($priorData = null);
    
    /**
     * Restore a serialised session of the current section
     * 
     * @param string $jsonString
     * @return CatSession
     */
    public function restoreSession($jsonString);
    
    /**
     * Get Section Identifier.
     * 
     * Returns the identifier of the section.
     * 
     * @return string
     */
    public function getSectionId();
}
