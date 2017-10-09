<?php

namespace Tests\AppBundle\Controller;

class CriteriaControllerTest extends BaseHelperClass {

    private $criteriaId;

    public function testAll() {
        $this->_testNewCriteria();
        $this->_testBadRequestsNewCriteria();
        $this->_testGetSingleCriteria();
        $this->_testGetAllCriteria();
        $this->_testModifyCriteria();
        $this->_testBadRequestsModifyCriteria();
        $this->_testDeleteCriteria();
        $this->_testBadRequestsDeleteCriteria();
    }

    private function _testNewCriteria() {
        $response = $this->getResponse(
            'POST',
            '/api/criteria/new/',
            json_encode([
                "keyword" => 'new criteria',
                "score" => 999
            ])
        );
        $this->assertEquals(200, $response['code']);
 
        $decodedBody = json_decode($response['body']);
        $this->assertNotNull($decodedBody->id);

        $this->criteriaId = $decodedBody->id;
    }

    private function _testBadRequestsNewCriteria() {
        $badRequests = [
            '{"bad: json": ¨',
            json_encode(["keyword" => 'new criteria']),
            json_encode(["score" => 123]),
            json_encode(["keyword" => "abctesting123", "score" => 'score must be int']),
            // new criteria already exists
            json_encode(["keyword" => "new criteria", "score" => 123]),
        ];

        $this->assertBadRequests('/api/criteria/new/', 'POST', $badRequests);
    }

    private function _testGetSingleCriteria() {
        $response = $this->getResponse(
            'GET',
            '/api/criteria?id=' . $this->criteriaId
        );
        $this->assertCorrectlyRecoveredCriteria($response);

        $response = $this->getResponse(
            'GET',
            '/api/criteria?keyword=new%20criteria'
        );
        $this->assertCorrectlyRecoveredCriteria($response);

        $response = $this->getResponse(
            'GET',
            '/api/criteria?score=999'
        );
        $this->assertCorrectlyRecoveredCriteria($response);
    }

    private function _testGetAllCriteria() {
        $response = $this->getResponse(
            'GET',
            '/api/criteria'
        );
        $this->assertEquals(200, $response['code']);
        $decodedBody = json_decode($response['body']);
        $this->assertEquals(TRUE, is_array($decodedBody));
        $this->assertNotNull($decodedBody[0]->keyword);
        $this->assertNotNull($decodedBody[0]->score);
    }

    private function _testModifyCriteria() {
        $response = $this->getResponse(
            'POST',
            '/api/criteria/modify/',
            json_encode([
                "id" => $this->criteriaId,
                "keyword" => 'modified criteria',
                "score" => -100
            ])
        );
        $this->assertEquals(200, $response['code']);
    }

    private function _testBadRequestsModifyCriteria() {
        $badRequests = [
            '{"bad: json": ¨',
            json_encode(["keyword" => 'new criteria']),
            json_encode(["id" => "id must be an integer"]),
            json_encode(["id" => 1, "score" => 'score must be int']),
            json_encode(["id" => -123, "score" => 1]),
        ];

        $this->assertBadRequests('/api/criteria/modify/', 'POST', $badRequests);
    }

    private function _testDeleteCriteria() {
        $response = $this->getResponse(
            'DELETE',
            '/api/criteria/delete/' . $this->criteriaId
        );
        $this->assertEquals(200, $response['code']);
    }

    private function _testBadRequestsDeleteCriteria() {
        $this->assertBadRequests('/api/criteria/delete/-123', 'DELETE', ['123']);
    }

    private function assertCorrectlyRecoveredCriteria($response) {
        $this->assertEquals(200, $response['code']);
        $decodedBody = json_decode($response['body']);

        $this->assertNotNull($decodedBody[0]->keyword);
        $this->assertNotNull($decodedBody[0]->score);
        $this->assertEquals('new criteria', $decodedBody[0]->keyword);
        $this->assertEquals(999, $decodedBody[0]->score);
    }

}
