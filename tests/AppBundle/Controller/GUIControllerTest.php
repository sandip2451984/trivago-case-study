<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GUIControllerTest extends BaseHelperClass {

    public function testIndex() {
        $response = $this->getResponse('GET', '/');
        $this->assertEquals(200, $response['code']);
    }

}
