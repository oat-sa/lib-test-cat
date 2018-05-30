<?php
namespace oat\libCat\test;

use PHPUnit\Framework\TestCase;
use oat\libCat\CatEngine;
use oat\libCat\ims\v1p3\CatSession;
use oat\libCat\ims\v1p3\SessionContext;
use Prophecy\Prophet;
use oat\libCat\result\ResultVariable;

class CatSessionTest extends TestCase
{
    public function testPrepareResults()
    {
        $session = $this->getSession([]);
        $this->assertEquals([], $session->getItemResults());

        $session = $this->getSession(['testResult' => null]);
        $this->assertEquals([], $session->getItemResults());

        $session = $this->getSession(['testResult' => ['traceVariables' => null]]);
        $this->assertEquals([], $session->getItemResults());

        $session = $this->getSession([
            'testResult' => [
                'traceVariables' => [
                (new ResultVariable('fakeId', 'fakeType', 'fakeValue'))->jsonSerialize()
                ]
            ]
        ]);
        $this->assertEquals([], $session->getItemResults());
    }

    private function getSession($resultArray)
    {
        $prophet = new Prophet();

	$engine = $prophet->prophesize(CatEngine::class);
	$context = $prophet->prophesize(SessionContext::class);
        $data = [
            'sessionIdentifier' => 'fakeSessionId',
            'nextItems' => [
                'itemIdentifiers' => [],
                'stageLength' => 0
            ],
            'assessmentResult' => $resultArray,
            'sessionState' => 'fakestate',
        ];

	return new CatSession($engine->reveal(), 'fakeSectionId', $data, $context->reveal());    }
}
