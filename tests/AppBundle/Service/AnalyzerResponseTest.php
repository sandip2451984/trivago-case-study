<?php

namespace Tests\AppBundle\Service;

use AppBundle\Service\AnalyzerResponse;
use AppBundle\Entity\Criteria;
use AppBundle\Entity\Emphasizer;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AnalyzerResponseTest extends WebTestCase {

    private $AnalyzerResponse;

    public function __construct() {
        parent::__construct();
        self::bootKernel();
        $this->AnalyzerResponse = static::$kernel->getContainer()->get('AppBundle.AnalyzerResponse');
    }

    public function testAnalyzerResponse() {
        $this->_testAddTopic();
        $this->_testAlreadyExistingTopic();
        $this->_testSumScore();
        $this->_testInvalidSumScore();
        $this->_testGetTotalScore();
        $this->_testAddFoundCriteria();
        $this->_testInvalidAddFoundCriteria();
        $this->_testGetAllCriteria();
        $this->_testInvalidTopicForGetScore();
        $this->_testInvalidTopicForGetCriteria();
        $this->_testRemoveTopic();
        $this->_testGetFullResults();
        $this->_testClear();
    }

    private function _testAddTopic() {
        $topics = ['hotel', 'bathroom'];

        foreach ($topics as $topic) {
            $this->AnalyzerResponse->addTopic($topic);
        }

        $this->assertArraySubset($topics, $this->AnalyzerResponse->getTopics());
        
        foreach ($topics as $topic) {
            $this->assertEquals(0, $this->AnalyzerResponse->getScore($topic));
        }
    }

    private function _testAlreadyExistingTopic() {
        $this->AnalyzerResponse->addTopic('bar');
        $this->AnalyzerResponse->addTopic('bar');

        $this->assertEquals(['hotel', 'bathroom', 'bar'], $this->AnalyzerResponse->getTopics());
    }

    private function _testSumScore() {
        $this->AnalyzerResponse->sumScore('hotel', 3);
        $this->assertEquals(3, $this->AnalyzerResponse->getScore('hotel'));

        $this->AnalyzerResponse->sumScore('bar', -2);
        $this->assertEquals(3, $this->AnalyzerResponse->getScore('hotel'));
    }

    private function _testInvalidSumScore() {
        try {
            $this->AnalyzerResponse->sumScore('non-existant topic', 123);
        } catch (\Exception $e) {
            $this->assertEquals($e->getMessage(), 'Topic does not exist.');
        }
    }

    private function _testGetTotalScore() {
        $totalScore = $this->AnalyzerResponse->getScore();
        $this->assertEquals(1, $totalScore);
    }

    private function _testAddFoundCriteria() {
        $criteria = new Criteria();
        $criteria->setKeyword('good');

        $this->AnalyzerResponse->addCriteria('hotel', $criteria, NULL, TRUE);
        $this->assertEquals('good', $this->AnalyzerResponse->getCriteria('hotel')[0]['entity']->getKeyword());
        $this->assertEquals(TRUE, $this->AnalyzerResponse->getCriteria('hotel')[0]['negated']);

        $criteria = new Criteria();
        $criteria->setKeyword('bad');
        $emphasizer = new Emphasizer();
        $emphasizer->setName('astonishingly');

        $this->AnalyzerResponse->addCriteria('bar', $criteria, $emphasizer);
        $this->assertEquals('bad', $this->AnalyzerResponse->getCriteria('bar')[0]['entity']->getKeyword());
        $this->assertEquals(FALSE, $this->AnalyzerResponse->getCriteria('bar')[0]['negated']);
        $this->assertEquals('astonishingly', $this->AnalyzerResponse->getCriteria('bar')[0]['emphasizer']->getName());
    }

    private function _testInvalidAddFoundCriteria() {
        try {
            $this->AnalyzerResponse->addCriteria('non-existant topic', new Criteria());
        } catch (\Exception $e) {
            $this->assertEquals($e->getMessage(), 'Topic does not exist.');
        }
    }

    private function _testGetAllCriteria() {
        $criteria = $this->AnalyzerResponse->getCriteria();
        $names = [];
        foreach ($criteria as $criterion) {
            $names[] = $criterion['entity']->getKeyword();
        }
        $this->assertArraySubset(['good', 'bad'], $names);
    }

    private function _testInvalidTopicForGetScore() {
        try {
            $this->AnalyzerResponse->getCriteria('non-existant topic');
        } catch (\Exception $e) {
            $this->assertEquals($e->getMessage(), 'Topic does not exist.');
        }
    }

    private function _testInvalidTopicForGetCriteria() {
        try {
            $this->AnalyzerResponse->getScore('non-existant topic');
        } catch (\Exception $e) {
            $this->assertEquals($e->getMessage(), 'Topic does not exist.');
        }
    }

    private function _testRemoveTopic() {
        $this->AnalyzerResponse->addTopic('staff');
        $this->AnalyzerResponse->removeTopic('staff');

        $this->assertEquals(['hotel', 'bathroom', 'bar'], $this->AnalyzerResponse->getTopics());
    }

    private function _testGetFullResults() {
        $hotelCriteria = new Criteria();
        $hotelCriteria->setKeyword('good');

        $barCriteria = new Criteria();
        $barCriteria->setKeyword('bad');
        $barEmphasizer = new Emphasizer();
        $barEmphasizer->setName('astonishingly');
        $expectedResult = [
            'hotel' => [
                'score' => 3,
                'criteria' => [[
                    'entity' => $hotelCriteria,
                    'emphasizer' => NULL,
                    'negated' => TRUE
                ]]
            ],
            'bathroom' => [
                'score' => 0,
                'criteria' => []
            ],
            'bar' => [
                'score' => -2,
                'criteria' => [[
                    'entity' => $barCriteria,
                    'emphasizer' => $barEmphasizer,
                    'negated' => FALSE
                ]]
            ]
        ];

        $result = $this->AnalyzerResponse->getFullResults();
        $this->assertEquals(json_encode($expectedResult), json_encode($result));

        $serializedResult = $this->AnalyzerResponse->getFullResults(TRUE);
        $this->assertEquals('{"hotel":{"score":3,"criteria":[{"entity":{"keyword":"good"},"negated":true}]},"bathroom":{"score":0,"criteria":[]},"bar":{"score":-2,"criteria":[{"entity":{"keyword":"bad"},"emphasizer":{"name":"astonishingly","score_modifier":0},"negated":false}]}}', $serializedResult);
    }

    private function _testClear() {
        $this->AnalyzerResponse->clear();
        $result = $this->AnalyzerResponse->getFullResults();
        $this->assertEquals(0, count($result));
    }

}
