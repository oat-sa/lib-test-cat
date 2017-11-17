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

use oat\libCat\CatSession;
use oat\libCat\result\ResultVariable;
use oat\libCat\result\ItemResult;
use oat\libCat\result\TestResult;

/**
 * Implementation of the Echoadapt session
 *
 * @author Joel Bout, <joel@taotesting.com>
 */
class EchoAdaptSession implements CatSession
{
    /** @var  EchoAdaptEngine */
    private $engine;

    private $sectionId;

    private $testTakerSessionId;

    private $nextItems;

    private $numberOfItemsInNextStage;

    private $linear;

    /** @var array The brut assessment result from the engine */
    private $assesmentResult = [];

    /** @var ItemResult The item results retrieved from last cat engine call */
    private $itemResults;

    /** @var TestResult The test results retrieved from last cat engine call */
    private $testResults;

    /** @var string The current session item id */
    private $currentItemId = null;

    private $sessionState;

    /**
     * EchoAdaptSession constructor.
     *
     * @param $engine
     * @param $sectionId
     * @param $testTakerSessionId
     * @param $nextItems
     * @param $numberOfItemsInNextStage
     * @param $linear
     * @param $assesmentResult
     * @param $sessionState
     */
    public function __construct($engine, $sectionId, $testTakerSessionId, $nextItems, $numberOfItemsInNextStage, $linear, $assesmentResult, $sessionState)
    {
        $this->engine = $engine;
        $this->sectionId = $sectionId;
        $this->testTakerSessionId = $testTakerSessionId;
        $this->nextItems = $nextItems;
        $this->numberOfItemsInNextStage = $numberOfItemsInNextStage;
        $this->linear = $linear;
        $this->assesmentResult = $assesmentResult;
        $this->sessionState = $sessionState;
    }

    /**
     * (non-PHPdoc)
     * @see \oat\libCat\CatSession::getTestMap()
     */
    public function getTestMap($results = [])
    {
        if (!empty($results)) {
            $data = $this->engine->call(
                'tests/'.$this->sectionId.'/test_taker_sessions/'.$this->testTakerSessionId.'/results',
                'POST',
                [
                    "results" => $this->filterResults($results),
                    "sessionState" => $this->sessionState
                ]
            );

            $this->nextItems = $data['nextItems'];
            $this->numberOfItemsInNextStage = $data['numberOfItemsInNextStage'];
            $this->linear = $data['linear'];
            $this->sessionState = $data['sessionState'];
            $this->assesmentResult = $data['assesmentResult'];
        }

        return $this->nextItems;
    }

    /**
     * Get the result associated to the item
     *
     * @return ItemResult
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
    public function getTestResults()
    {
        if (!$this->testResults) {
            $this->prepareResults();
        }
        return $this->testResults;
    }

    /**
     * (non-PHPdoc)
     * @see JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize()
    {
        return [
            'testTakerSessionId' => $this->testTakerSessionId,
            'nextItems' => $this->nextItems,
            'numberOfItemsInNextStage' => $this->numberOfItemsInNextStage,
            'linear' => $this->linear,
            'assesmentResult' => $this->assesmentResult,
            'sessionState' => $this->sessionState,
        ];
    }

    /**
     * Get Test Taker Session Identifier.
     *
     * Returns the unique identifier representing both the Echo Adapt session and the test taker taking the session.
     *
     * @return string
     */
    public function getTestTakerSessionId()
    {
        return $this->testTakerSessionId;
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

        $this->itemResults = new ItemResult($this->currentItemId, $this->restoreVariables($result['itemResults']));
        $this->testResults = new TestResult($this->restoreVariables($result['testResult']));
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
