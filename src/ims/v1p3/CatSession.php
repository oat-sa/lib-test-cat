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
use oat\libCat\result\ResultVariable;
use oat\libCat\result\ItemResult;
use oat\libCat\result\TestResult;
use oat\libCat\result\AbstractResult;

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
    private $assessmentResult = [];

    /** @var ItemResult[] The item results retrieved from last cat engine call */
    private $itemResults;

    /** @var TestResult The test result retrieved from last cat engine call */
    private $testResult;

    /** @var string The current session item id */
    private $currentItemId = null;

    private $sessionState;

    /** @var SessionContext  */
    private $context;

    /**
     * CatSession constructor.
     *
     * @param $engine
     * @param $settings
     * @param $data
     */
    public function __construct($engine, $sectionId, $data, SessionContext $context)
    {
        $this->engine = $engine;
        $this->sectionId = $sectionId;
        $this->sessionIdentifier = $data['sessionIdentifier'];
        $this->nextItems = $data['nextItems']['itemIdentifiers'];
        $this->stageLength = $data['nextItems']['stageLength'];
        $this->assessmentResult = isset($data['assessmentResult']) ? $data['assessmentResult'] : [];
        $this->sessionState = $data['sessionState'];
        $this->context = $context;
    }

    /**
     * (non-PHPdoc)
     * @see \oat\libCat\CatSession::getTestMap()
     *
     * @param AbstractResult[] $results
     * @return string[]
     * @throws \oat\libCat\exception\CatEngineConnectivityException
     */
    public function getTestMap($results = [])
    {
        if (!empty($results) && $this->sessionState !== null) {
            $context = [];
            $requestData = ResultFormatter::formatResultData([
                'assessmentResult' => [
                    'context' => $this->context,
                    'itemResult' => $this->filterItemResults($results),
                ],
                'sessionState' => $this->sessionState
            ]);

            $data = $this->engine->call(
                'sections/'.$this->sectionId.'/sessions/'.$this->sessionIdentifier.'/results',
                'POST',
                $requestData
            );

            if (empty($data)) {
                throw new CatEngineConnectivityException('Empty response from CAT engine');
            }

            $this->nextItems = $data['nextItems']['itemIdentifiers'];
            $this->stageLength = $data['nextItems']['stageLength'];
            if (isset($data['assessmentResult'])) {
                $this->assessmentResult = $data['assessmentResult'];
            }
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
     * @see \JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize()
    {
        return [
            'data' => [
                'sessionIdentifier' => $this->sessionIdentifier,
                'nextItems' => [
                    'itemIdentifiers' => $this->nextItems,
                    'stageLength' => $this->stageLength,
                ],
                'assessmentResult' => $this->assessmentResult,
                'sessionState' => $this->sessionState,
            ],
            'context' => $this->context
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
     * Set the item, test and assessment results
     *
     */
    private function prepareResults()
    {
        $result = $this->assessmentResult;
        if (empty($result)) {
            $this->itemResults = [];
            return;
        }

        /**
         * Workaround to move item related variables to ItemResults from TestResult
         */
        if (is_array($result['testResult'])) {
            foreach ($result['testResult'] as $variableType => $variables) {
                foreach ($variables as $key => $misLocatedVariable) {
                    if (strpos($misLocatedVariable['identifier'], 'CURRENT_') === 0) {
                        $result['itemResults'][$variableType][] = $misLocatedVariable;
                        unset($result['testResult'][$variableType][$key]);
                    }
                }
            }
            $this->testResult = new TestResult($this->restoreVariables($result['testResult']));
        } else {
            $this->testResult = [];
        }

        if (is_null($this->currentItemId) || !isset($result['itemResults'])) {
            $this->itemResults = [];
        } else {
            $this->itemResults = [new ItemResult($this->currentItemId, $this->restoreVariables($result['itemResults']))];
        }
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
                $variable['values'] = $variable['value'];
                unset($variable['value']);
                foreach ($variable['values'] as &$val) {
                    $val['valueString'] = $val['value'];
                    unset($val['value']);
                }
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
    private function filterItemResults($results)
    {
        foreach ($results as $key => $result) {
            $scoreOnly = [];
            foreach ($result->getVariables() as $variable) {
                if (strtolower($variable->getId()) == 'score') {
                    $scoreOnly[] = $variable;
                }
            }
            $results[$key] = new ItemResult($result->getItemRefId(), $scoreOnly, $result->getTimestamp());
        }
        // Get the last result as current item id
        if (isset($result)) {
            $this->currentItemId = $result->getItemRefId();
        }

        return $results;
    }

}
