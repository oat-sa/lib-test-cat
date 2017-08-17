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
 * Interface CatSessionState
 * @package oat\libCat
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
interface CatSessionState extends \JsonSerializable
{
    /**
     * Get array of identifiers of previously seen items
     * @return array
     */
    public function getPreviouslySeenItems();

    /**
     * Get array of identifiers of items to be shown
     * @return array
     */
    public function getNextItems();

    /**
     * Get array of prevously shown items plus items to be shown
     * Represents total items list of section in current state
     * @return array
     */
    public function getShadowTest();

    /**
     * Get submitted item stores
     * @return array
     */
    public function getItemScores();
}
