<?php

namespace Tests\AppBundle\Controller;

class EmphasizerSControllerTest extends BaseHelperClass {

    private $emphasizerId;

    public function testAll() {
        $this->_testNewEmphasizer();
        $this->_testBadRequestsNewEmphasizer();
        $this->_testGetSingleEmphasizer();
        $this->_testGetAllEmphasizers();
        $this->_testModifyEmphasizer();
        $this->_testBadRequestsModifyEmphasizer();
        $this->_testDeleteEmphasizer();
        $this->_testBadRequestsDeleteEmphasizer();
    }


    private function _testNewEmphasizer() {
        $response = $this->getResponse(
            'POST',
            '/api/emphasizers/new/',
            json_encode([
                "name" => 'new emphasizer',
                "score_modifier" => .4
            ])
        );
        $this->assertEquals(200, $response['code']);
 
        $decodedBody = json_decode($response['body']);
        $this->assertNotNull($decodedBody->id);

        $this->emphasizerId = $decodedBody->id;
    }

    private function _testGetSingleEmphasizer() {
        $response = $this->getResponse(
            'GET',
            '/api/emphasizers/?id=' . $this->emphasizerId
        );
        $this->assertEquals(200, $response['code']);

        $decodedBody = json_decode($response['body'])[0];
        $this->assertNotNull($decodedBody->name);
        $this->assertNotNull($decodedBody->score_modifier);
        $this->assertEquals('new emphasizer', $decodedBody->name);
        $this->assertEquals(0.4, $decodedBody->score_modifier);

        
        $response = $this->getResponse(
            'GET',
            '/api/emphasizers/?name=new%20emphasizer'
        );
        $this->assertEquals(200, $response['code']);

        $decodedBody = json_decode($response['body'])[0];
        $this->assertNotNull($decodedBody->name);
        $this->assertNotNull($decodedBody->score_modifier);
        $this->assertEquals('new emphasizer', $decodedBody->name);
        $this->assertEquals(0.4, $decodedBody->score_modifier);
    }

    private function _testGetAllEmphasizers() {
        $response = $this->getResponse(
            'GET',
            '/api/emphasizers/'
        );
        $this->assertEquals(200, $response['code']);
        $decodedBody = json_decode($response['body']);
        $this->assertEquals(TRUE, is_array($decodedBody));
        $this->assertNotNull($decodedBody[0]->name);
        $this->assertNotNull($decodedBody[0]->score_modifier);
    }

    private function _testModifyEmphasizer() {
        $response = $this->getResponse(
            'POST',
            '/api/emphasizers/modify/',
            json_encode([
                "id" => $this->emphasizerId,
                "name" => 'modified emphasizer',
                "score_modifier" => 0.2
            ])
        );
        $this->assertEquals(200, $response['code']);
    }

    private function _testDeleteEmphasizer() {
        $response = $this->getResponse(
            'DELETE',
            '/api/emphasizers/delete/' . $this->emphasizerId
        );
        $this->assertEquals(200, $response['code']);
    }


    private function _testBadRequestsNewEmphasizer() {
        $badRequests = [
            '{"bad: json": ¨',
            json_encode(["name" => 'new emphasizer']),
            json_encode(["score_modifier" => 123]),
            json_encode(["name" => "abctesting123", "score_modifier" => 'score must be a number']),
            // new emphasizer already exists
            json_encode(["name" => "new emphasizer", "score_modifier" => 123]),
        ];

        $this->assertBadRequests('/api/emphasizers/new/', 'POST', $badRequests);
    }


    private function _testBadRequestsModifyEmphasizer() {
        $badRequests = [
            '{"bad: json": ¨',
            json_encode(["name" => 'new emphasizer']),
            json_encode(["id" => "id must be an integer"]),
            json_encode(["id" => 1, "score_modifier" => 'score_modifier must be a number']),
            json_encode(["id" => -123, "score" => 1]),
        ];

        $this->assertBadRequests('/api/emphasizers/modify/', 'POST', $badRequests);
    }

    private function _testBadRequestsDeleteEmphasizer() {
        $this->assertBadRequests('/api/emphasizers/delete/-123', 'DELETE', ['123']);
    }
}
