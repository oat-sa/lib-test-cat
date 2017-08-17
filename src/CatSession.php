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

use oat\libCat\result\ResultVariable;
use oat\libCat\result\ItemResult;
/**
 * Interface to describe a test taker session for a given section 
 *
 * @author Joel Bout, <joel@taotesting.com>
 */
interface CatSession extends \JsonSerializable
{
    /**
     * Returns the item reference ids of the next items to present
     * to the Testtaker for a given session and given results.
     *
     * This can modify the internal state of the session and requires
     * the session to be reserialized
     *
     * @param ItemResult $results
     * @return string[]
     */
    public function getTestMap($results = []);
    
    /**
     * Returns testresults provided by the engine
     * Result ids might not be unique across the test
     *
     * @return ResultVariable
     */
    public function getResults();
    
    /**
     * Get Test Taker Session Identifier.
     * 
     * Returns the unique identifier representing both the adaptive session and the test taker taking the session.
     * 
     * @return string
     */
    public function getTestTakerSessionId();

    /**
     * Get test session state instance
     *
     * @return CatSessionState
     */
    public function getSessionState();
}
