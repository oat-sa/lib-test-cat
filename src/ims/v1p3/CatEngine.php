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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA
 */

namespace oat\libCat\ims\v1p3;

use oat\libCat\AbstractCatEngine;

/**
 * Implementation of the IMS CAT API v1.3 engine
 */
class CatEngine extends AbstractCatEngine
{
    /**
     * @inheritdoc
     */
    protected function createSection($adaptiveSettingsRef, $qtiUsageData = null, $qtiMetaData = null)
    {
        return new CatSection($this, $adaptiveSettingsRef, $qtiUsageData, $qtiMetaData);
    }

    /**
     * (non-PHPdoc)
     * @see \oat\libCat\CatEngine::restoreSection()
     */
    public function restoreSection($data)
    {
        if (is_string($data)) {
            $data = json_decode($data);
        }
        return new CatSection($this, $data['adaptiveSettingsRef'], $data['qtiUsagedataRef'], $data['qtiMetadataRef'], $data['sectionId']);
    }
}
