<?php

namespace Tests\AppBundle\Controller;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class ReviewsControllerTest extends BaseHelperClass {

    private $reviewId;

    public function testAll() {
        $this->_testTestAnalyzer();
        $this->_testNewReview();
        $this->_testBadRequestNewReview();
        $this->_testAnalyzeReview();
        $this->_testBadRequestAnalyzeReview();
        $this->_testAnalyzeALL();
        $this->_testGetReviews();
        $this->_testUploadReviews();
        $this->_testModifyReview();
        $this->_testBadRequestModifyReview();
        $this->_testDeleteReview();
        $this->_testBadRequestDeleteReview();
    }

    private function _testTestAnalyzer() {
        $response = $this->getResponse(
            'POST',
            '/api/reviews/testAnalyzer/',
            'Hotel is very bad. Restaurant is not good.'
        );
        $this->assertEquals(200, $response['code']);

        $decodedBody = json_decode($response['body']);

        // since the analyzer is fully tested in tests/AppBundle/Service/DefaultAnalyzerTest.php,
        // there is no need to test any more than this here.
        $this->assertNotNull($decodedBody->hotel);
        $this->assertNotNull($decodedBody->restaurant);
    }

    private function _testNewReview() {
        $response = $this->getResponse(
            'POST',
            '/api/reviews/new/',
            json_encode([
                'text' => $this->getNewReview()
            ])
        );
        $this->assertEquals(200, $response['code']);
 
        $decodedBody = json_decode($response['body']);
        $this->assertGreaterThan(0, $decodedBody->id);

        $this->reviewId = $decodedBody->id;
    }

    private function _testBadRequestNewReview() {
        $badRequests = [
            json_encode([
                'missingtext' => '123'
            ]),
            json_encode([
                'text' => ''
            ])
        ];

        foreach ($badRequests as $badRequest) {
            $response = $this->getResponse(
                'POST',
                '/api/reviews/new/',
                $badRequest
            );
            $this->assertEquals(400, $response['code']);
        }
    }

    private function _testModifyReview() {
        $response = $this->getResponse(
            'POST',
            '/api/reviews/modify/',
            json_encode([
                'id' => $this->reviewId,
                'text' => 'modified review'
            ])
        );
        $this->assertEquals(200, $response['code']);
    }

    private function _testBadRequestModifyReview() {
        $badRequests = [
            json_encode([
                'id' => -999,
                'text' => 'modified review'
            ]),
            json_encode([
                'aaaaid' => '-999',
                'text' => 'modified review'
            ]),
            json_encode([
                'id' => 'must be integer',
                'text' => 'modified review'
            ])
        ];

        foreach ($badRequests as $badRequest) {
            $response = $this->getResponse(
                'POST',
                '/api/reviews/modify/',
                $badRequest
            );
            $this->assertEquals(400, $response['code']);
        }
    }

    private function _testAnalyzeReview() {
        $response = $this->getResponse(
            'POST',
            '/api/reviews/analyze/' . $this->reviewId
        );
        $this->assertEquals(200, $response['code']);
        $decodedBody = json_decode($response['body']);
        $this->assertNotNull($decodedBody->analysis);
        $this->assertEquals(2, count($decodedBody->analysis));
    }

    private function _testBadRequestAnalyzeReview() {
        $response = $this->getResponse(
            'POST',
            '/api/reviews/analyze/-999'
        );
        $this->assertEquals(400, $response['code']);
    }

    private function _testAnalyzeALL() {
        $response = $this->getResponse(
            'POST',
            '/api/reviews/analyze/all/'
        );
        $this->assertEquals(200, $response['code']);
    }

    private function _testGetReviews() {
        $response = $this->getResponse(
            'GET',
            '/api/reviews/'
        );
        $this->assertCorrectlyRecoveredReviews($response);
        
        $response = $this->getResponse(
            'GET',
            '/api/reviews/?text=hotel'
        );
        $this->assertCorrectlyRecoveredReviews($response);
        
        $response = $this->getResponse(
            'GET',
            '/api/reviews/?total_score=<0'
        );
        $this->assertCorrectlyRecoveredReviews($response);

        $response = $this->getResponse(
            'GET',
            '/api/reviews/?total_score=100'
        );
        $this->assertCorrectlyRecoveredReviews($response);
    }

    private function _testUploadReviews() {
        $response = $this->getResponse(
            'PUT',
            '/api/reviews/upload/',
            '',
            [],
            $this->getCSVFile('test_reviews')
        );
        $this->assertEquals(200, $response['code']);
    }

    private function _testDeleteReview() {
        $response = $this->getResponse(
            'DELETE',
            '/api/reviews/delete/' . $this->reviewId
        );
        $this->assertEquals(200, $response['code']);
    }

    private function _testBadRequestDeleteReview() {
        $response = $this->getResponse(
            'DELETE',
            '/api/reviews/delete/-9999'
        );
        $this->assertEquals(400, $response['code']);
    }

    private function getCSVFile(string $filename) : array {
        $filepath = __DIR__ . '/'.$filename.'.csv';
        $csv = new UploadedFile($filepath, $filepath, 'text/csv', filesize($filepath));
        return ['csvFile' => $csv];
    }

    private function getNewReview() : string {
        return 'Staff is so helpful and very friendly. Pool is so dirty though.';
    }

    private function assertCorrectlyRecoveredReviews($response) {
        $this->assertEquals(200, $response['code']);
        $decodedBody = json_decode($response['body']);
        $this->assertEquals(TRUE, is_array($decodedBody));
    }
}
