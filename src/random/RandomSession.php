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

namespace oat\libCat\random;

use oat\libCat\CatSession;
use oat\libCat\result\ResultVariable;

class RandomSession implements CatSession
{
    
    private $data; 

    public function __construct($data){
        $this->data = $data;
    }

    public function getTestMap($results = [])
    {
        $output = array_rand($this->data, rand(2, count($this->data)));
        if(!is_array($output)){
            return [$output];
        }
        shuffle($output);
        return array_map(function($index){
            return $this->data[$index];
        }, $output);
    }
    
    /**
     * Returns testresults provided by the engine
    */
    public function getResults()
    {
        $variables = [];
        return $variables;
    }
    
    public function jsonSerialize()
    {
        return [
        ];
    }
}
