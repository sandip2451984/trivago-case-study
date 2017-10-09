<?php

namespace AppBundle\Service;

use AppBundle\Entity\Criteria;
use AppBundle\Entity\Emphasizer;

use JMS\Serializer\SerializerInterface;

final class AnalyzerResponse {

    private $Serializer;

    private $finalScore;

    public function __construct(SerializerInterface $s) {
        $this->Serializer = $s;
        $this->finalScore = [];
    }

    public function addTopic(string $topic) : void {
        if ($this->topicExists($topic)) return;

        $this->finalScore[$topic] = [
            'score' => 0,
            'criteria' => []
        ];
    }

    public function sumScore(string $topic, int $score) : int {
        if (! $this->topicExists($topic))
            throw new \Exception('Topic does not exist.');
        
        $this->finalScore[$topic]['score'] += $score;
        return $this->finalScore[$topic]['score'];
    }

    public function addCriteria(string $topic, Criteria $criteria, ?Emphasizer $emphasizer = NULL, ?bool $negated = FALSE) : void {
        if (! $this->topicExists($topic))
            throw new \Exception('Topic does not exist.');

        $this->finalScore[$topic]['criteria'][] = [
            'entity' => $criteria,
            'emphasizer' => $emphasizer,
            'negated' => $negated
        ];
    }

    public function getTopics() : array {
        return array_keys($this->finalScore);
    }

    public function getScore(?string $topic = NULL) : int {
        if ($topic) {
            if (! $this->topicExists($topic))
                throw new \Exception('Topic does not exist.');
            
            $score = $this->finalScore[$topic]['score'];
        } else {
            $totalScore = 0;
            foreach ($this->finalScore as $topic => $topicScore) {
                $totalScore += $topicScore['score'];
            }
            $score = $totalScore;
        }

        return $score;
    }

    public function getCriteria(?string $topic = NULL) : array {
        if ($topic) {
            if (! $this->topicExists($topic))
                throw new \Exception('Topic does not exist.');
            
            $criteria = $this->finalScore[$topic]['criteria'];
        } else {
            $criteria = [];
            foreach ($this->finalScore as $topic => $topicScore) {
                $criteria = array_merge($criteria, $topicScore['criteria']);
            }
        }

        return $criteria;
    }

    public function removeTopic(string $topic) : void {
        if (! $this->topicExists($topic)) return;

        unset($this->finalScore[$topic]);
    }

    public function getFullResults(bool $serialized = FALSE) {
        if ($serialized)
            return $this->Serializer->serialize($this->finalScore, 'json');
        else 
            return $this->finalScore;
    }
   
    public function clear() : void {
        $this->finalScore = [];
    } 

    private function topicExists(string $topic) : bool {
        return array_key_exists($topic, $this->finalScore);
    }

}