<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BaseHelperClass extends WebTestCase {

    protected $client;

    public function __construct() {
        parent::__construct();
        $this->client = static::createClient();
    }

    protected function getResponse(string $method, string $route, string $request = NULL, array $headers = [], array $file = []) : array {
        $this->client->request($method, $route, [], $file, $headers, $request);
        $response = $this->client->getResponse();
        return [
            'code' => $response->getStatusCode(),
            'body' => $response->getContent()
        ];
    }

    protected function assertBadRequests(string $route, string $method, array $badRequests) {
        foreach ($badRequests as $badRequest) {
            $response = $this->getResponse($method, $route, $badRequest);
            $this->assertEquals(400, $response['code']);
            $decodedBody = json_decode($response['body']);
            $this->assertNotNull($decodedBody->error);
        }
    }

}
