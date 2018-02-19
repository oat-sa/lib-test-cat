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

use oat\libCat\CatSession as CatSessionInterface;
use oat\libCat\exception\CatEngineConnectivityException;
use oat\libCat\ims\v1p3\CatEngine;
use oat\libCat\result\ResultVariable;
use oat\libCat\result\ItemResult;
use oat\libCat\result\TestResult;

/**
 * Implementation of the CatSession session
 */
class CatSession implements CatSessionInterface
{
    /** @var  CatEngine */
    private $engine;

    private $sessionIdentifier;

    private $sectionId;

    private $nextItems;

    private $stageLength;

    /** @var array The brut assessment result from the engine */
    private $assesmentResult = [];

    /** @var ItemResult[] The item results retrieved from last cat engine call */
    private $itemResults;

    /** @var TestResult The test result retrieved from last cat engine call */
    private $testResult;

    /** @var string The current session item id */
    private $currentItemId = null;

    private $sessionState;

    /**
     * CatSession constructor.
     *
     * @param $engine
     * @param $settings
     * @param $data
     */
    public function __construct($engine, $sectionId, $data)
    {
        $this->engine = $engine;
        $this->sectionId = $sectionId;
        $this->sessionIdentifier = $data['sessionIdentifier'];
        $this->nextItems = $data['nextItems']['itemIdentifiers'];
        $this->stageLength = $data['nextItems']['stageLength'];
        //$this->assesmentResult = isset($data['assesmentResult']) ? $data['assesmentResult'] : [];
        $this->sessionState = $data['sessionState'];
    }

    /**
     * (non-PHPdoc)
     * @see \oat\libCat\CatSession::getTestMap()
     *
     * @param array $results
     * @return string[]
     * @throws \oat\libCat\exception\CatEngineConnectivityException
     */
    public function getTestMap($results = [])
    {
        if (!empty($results) && $this->sessionState !== null) {

            $requestData = ResultFormatter::formatResultData([
                "results" => $this->filterResults($results),
                "sessionState" => $this->sessionState
            ]);

            $data = $this->engine->call(
                'sections/'.$this->sectionId.'/sessions/'.$this->sessionIdentifier,
                'POST',
                $requestData
            );

            if (empty($data)) {
                throw new CatEngineConnectivityException('Empty response from CAT engine');
            }

            $this->nextItems = $data['nextItems']['itemIdentifiers'];
            $this->stageLength = $data['nextItems']['stageLength'];
            //$this->assesmentResult = $data['assesmentResult'];
            $this->sessionState = $data['sessionState'];
        }

        return is_array($this->nextItems) ? $this->nextItems : [];
    }

    /**
     * Get the result associated to the item
     *
     * @return ItemResult[]
     */
    public function getItemResults()
    {
        if (!$this->itemResults) {
            $this->prepareResults();
        }
        return $this->itemResults;
    }

    /**
     * Get the result associated to the item
     *
     * @return TestResult
     */
    public function getTestResult()
    {
        if (!$this->testResult) {
            $this->prepareResults();
        }
        return $this->testResult;
    }

    /**
     * (non-PHPdoc)
     * @see JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize()
    {
        return [
            'sessionIdentifier' => $this->sessionIdentifier,
            'nextItems' => [
                'itemIdentifiers' => $this->nextItems,
                'stageLength' => $this->stageLength,
            ],
            //'assesmentResult' => $this->assesmentResult,
            'sessionState' => $this->sessionState
        ];
    }

    /**
     * Get Test Taker Session Identifier.
     *
     * Returns the unique identifier representing both the CAT session and the test taker taking the session.
     *
     * @return string
     */
    public function getTestTakerSessionId()
    {
        return $this->sessionIdentifier;
    }

    /**
     * Prepare the result to sort it by items and test
     *
     * Move item outcome variable from test to item result
     * Set the item, test and assesment results
     *
     */
    private function prepareResults()
    {
        $result = $this->assesmentResult;
        if (empty($result)) {
            return;
        }

        /**
         * Workaround to move item related variables to ItemResults from TestResult
         */
        foreach ($result['testResult'] as $variableType => $variables) {
            if (!empty($variables)) {
                foreach ($variables as $key => $misLocatedVariable) {
                    if (strpos($misLocatedVariable['identifier'], 'CURRENT_') === 0) {
                        $result['itemResults'][$variableType][] = $misLocatedVariable;
                        unset($result['testResult'][$variableType][$key]);
                    }
                }
            }
        }

        if (is_null($this->currentItemId)) {
            $this->itemResults = [];
        } else {
            $this->itemResults = [new ItemResult($this->currentItemId, $this->restoreVariables($result['itemResults']))];
        }

        $this->testResult = new TestResult($this->restoreVariables($result['testResult']));
    }

    /**
     * Unserialize variables array to Variable object
     *
     * @param array $result The incoming variables array (with first keys as types)
     * @return array An simple array of ResultVariable objects with type set
     */
    private function restoreVariables(array $result)
    {
        $restoredVariables = [];

        foreach ($result as $type => $variables) {
            if (empty($variables)) {
                continue;
            }

            switch ($type) {
                case 'templateVariables':
                    $variableType = ResultVariable::TEMPLATE_VARIABLE;
                    break;
                case 'responseVariables':
                    $variableType = ResultVariable::RESPONSE_VARIABLE;
                    break;
                case 'traceVariables':
                    $variableType = ResultVariable::TRACE_VARIABLE;
                    break;
                case 'outcomeVariables':
                default:
                    $variableType = ResultVariable::OUTCOME_VARIABLE;
                    break;
            }

            foreach ($variables as $variable) {
                $variable['variableType'] = $variableType;
                $restoredVariables[] = ResultVariable::restore($variable);
            }
        }

        return $restoredVariables;
    }

    /**
     * filter out non 'score' variables
     *
     * @param ItemResult[] $results
     * @return ItemResult[]
     */
    private function filterResults($results)
    {
        foreach ($results as $key => $result) {
            $scoreOnly = [];
            foreach ($result->getVariables() as $variable) {
                if (strtolower($variable->getId()) == 'score') {
                    $scoreOnly[] = $variable;
                }
            }
            $results[$key] = new ItemResult($result->getItemRefId(), $scoreOnly);
        }
        // Get the last result as current item id
        if (isset($result)) {
            $this->currentItemId = $result->getItemRefId();
        }

        return $results;
    }

}