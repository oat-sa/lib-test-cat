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

/**
 * Class SessionContext
 * @package oat\libCat\ims\v1p3
 * @author Aleh Hutnikau, <hutnikau@1pt>
 */
class SessionContext implements \JsonSerializable
{

    private $context;

    /**
     * SessionContext constructor.
     * @param array $context
     */
    public function __construct($context = [])
    {
        $this->context = $context;
    }

    /**
     * A unique identifier for the test candidate.
     * @return string|null
     */
    public function getSourcedId()
    {
        return isset($context['sourcedId']) ? $context['sourcedId'] : null;
    }

    /**
     * @return array
     */
    public function getSessionIdentifiers()
    {
        return isset($context['sessionIdentifiers']) ? $context['sessionIdentifiers'] : [];
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'sourcedId' => $this->getSourcedId(),
            'sessionIdentifiers' => $this->getSessionIdentifiers(),
        ];
    }
}