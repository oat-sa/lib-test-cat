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

use oat\libCat\CatSessionState;

/**
 * Class EchoAdaptSessionState
 * @package oat\libCat\custom
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class EchoAdaptSessionState implements CatSessionState
{
    /** @var string session state in initial (encoded) stae */
    private $state;

    /** @var array decoded session state */
    private $decodedState;

    /**
     * EchoAdaptSessionState constructor.
     * @param string $state
     */
    public function __construct($state)
    {
        $this->state = $state;
    }

    /**
     * @inheritdoc
     */
    public function getPreviouslySeenItems()
    {
        $this->getDecodedState()['previouslySeenItems'];
    }

    /**
     * @inheritdoc
     */
    public function getNextItems()
    {
        $this->getDecodedState()['nextItems'];
    }

    /**
     * @inheritdoc
     */
    public function getShadowTest()
    {
        $this->getDecodedState()['shadowTest'];
    }

    /**
     * @inheritdoc
     */
    public function getItemScores()
    {
        $this->getDecodedState()['itemScores'];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->state;
    }

    /**
     * @return string
     */
    public function jsonSerialize()
    {
        return $this->state;
    }

    /**
     * @return array
     */
    private function getDecodedState()
    {
        if ($this->decodedState === null) {
            $this->decodedState = json_decode(base64_decode($this->state), true);
        }
        return $this->decodedState;
    }
}
