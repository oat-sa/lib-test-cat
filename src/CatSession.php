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
     * to the Testtaker for a given session and given results
     *
     * @param ItemResult $results
     * @return string[]
     */
    public function getTestMap($results = []);
    
    /**
     * Returns testresults provided by the engine
     * @return ResultVariable
     */
    public function getResults();
}
