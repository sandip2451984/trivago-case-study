<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use ICanBoogie\Inflector as Pluralizator;

use AppBundle\Entity\Topic;

class DefaultAnalyzer implements IAnalyzer {

    private $DoctrineManager;
    private $ServiceContainer;
    private $TypoFixer;
    private $AnalyzerResponse;

    private $topics;
    private $criteria;
    private $emphasizers;
    private $lastKnownTopic;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container, AnalyzerResponse $ar, ?ITypoFixer $tf = NULL) {
        $this->DoctrineManager = $em;
        $this->ServiceContainer = $container;
        $this->AnalyzerResponse = $ar;
        $this->TypoFixer = $tf;

        $this->setCriteria()->setTopics()->setEmphasizers();
    }

    public function analyze(string $review) : AnalyzerResponse {
        $this->TypoFixer && $this->TypoFixer->fix($review);

        $divisions = $this->getSentencesDivisions(strtolower($review));
        $this->lastKnownTopic = Topic::UNKNOWN_TOPIC_NAME;
        $this->AnalyzerResponse->clear();

        foreach ($divisions as $division) {
            $topic = $this->getDivisionTopic($division);

            $this->AnalyzerResponse->addTopic($topic);
            $this->canReassignUnkownTopicCriteria($topic) && $this->reassignUnknownTopicCriteria($topic);
            $this->setTopicCriteriaAndScore($topic, $division);
            $this->lastKnownTopic = $topic;
        }

        $this->removeTopicsWithoutCriteria();

        return $this->AnalyzerResponse;
    }

    private function getSentencesDivisions(string $review) : array {
        $sentences = explode('.', $review);
        $divisionChars = ['!', '?', ',', ' and ', '&', ' but ', ':', ';'];
        foreach ($divisionChars as $separator) {
            $sentenceDivisions = [];
            foreach ($sentences as $sentence) {
                $sentenceDivisions = array_merge($sentenceDivisions, explode($separator, $sentence));
            }
            $sentences = $sentenceDivisions;
        }
        return $sentences;
    }

    private function getDivisionTopic(string $division) : string {
        foreach ($this->topics as $topicEntity) {
            if ($this->topicExistsInDivision($topicEntity->getName(), $division)) 
                return $topicEntity->getName();

            $aliases = $topicEntity->getAliases();
            foreach ($aliases as $topicAliasEntity) {
                if ($this->topicExistsInDivision($topicAliasEntity->getAlias(), $division))
                    return $topicEntity->getName();
            }
        }
        
        return $this->lastKnownTopic;
    }
    
    private function topicExistsInDivision(string $topic, string $division) : bool {
        return (
            preg_match('/\\b'.$topic.'\\b/i', $division) ||
            preg_match('/\\b'.$this->pluralize($topic).'\\b/i', $division)
        );
    }

    private function canReassignUnkownTopicCriteria(string $newTopic) : bool {
        return (
            $newTopic !== Topic::UNKNOWN_TOPIC_NAME &&
            $this->lastKnownTopic === Topic::UNKNOWN_TOPIC_NAME &&
            in_array(Topic::UNKNOWN_TOPIC_NAME, $this->AnalyzerResponse->getTopics())
        );
    }

    private function reassignUnknownTopicCriteria(string $correctTopic) : void {
        $unknownTopicCriteria = $this->AnalyzerResponse->getCriteria(Topic::UNKNOWN_TOPIC_NAME);
        foreach ($unknownTopicCriteria as $criteria) {
            $this->AnalyzerResponse->addCriteria($correctTopic, $criteria['entity'], $criteria['emphasizer'], $criteria['negated']);
        }
        $this->AnalyzerResponse->sumScore($correctTopic, $this->AnalyzerResponse->getScore(Topic::UNKNOWN_TOPIC_NAME));
        $this->AnalyzerResponse->removeTopic(Topic::UNKNOWN_TOPIC_NAME);
    }

    private function setTopicCriteriaAndScore(string $topic, string $division) : void {
        foreach ($this->criteria as $criteriaEntity) {
            $keyword = $criteriaEntity->getKeyword();
            if (! $this->criteriaExistsInDivision($keyword, $division)) continue;

            $score = $criteriaEntity->getScore();
            if ($this->isCriteriaNegated($keyword, $division)) {
                $score = $this->getNegatedCriteriaScore($score);
                $negated = TRUE;
            } else {
                $negated = FALSE;
                if ($emphasizer = $this->getEmphasizer($keyword, $division))
                    $score += round($score * $emphasizer->getScoreModifier());
            }
            $this->AnalyzerResponse->addCriteria($topic, $criteriaEntity, $emphasizer ?? NULL, $negated);
            $this->AnalyzerResponse->sumScore($topic, $score);
        }
    }


    private function criteriaExistsInDivision(string $keyword, string $division) : bool {
        // Since a criteria keyword can have more than 1 word, for example "going to come back", 
        // we need to check that in order to use stripos for that criteria or a regexp with word boundaries (/b)
        // for single word criteria.
        if (count(str_word_count($keyword, 1)) > 1)
            return stripos($division, $keyword) !== FALSE;
        else
            return preg_match('/\\b'.$keyword.'\\b/i', $division);
    }

    private function isCriteriaNegated(string $keyword, string $division) : bool {
        if ($this->criteriaKeywordHasNegators($keyword)) return FALSE;

        $divisionWords = str_word_count($division, 1);
        foreach (DefaultAnalyzerConstants::NEGATORS as $negator) {
            $negatorIndex = array_search($negator, $divisionWords);

            if ($negatorIndex === FALSE) continue;

            // Example:
            // "Not only this is a good place, but it has nice food"
            // In this case, the "not" is not negating the "good" criteria.
            // That's why we check the word right after the negator for this particular case.
            if ($divisionWords[$negatorIndex+1] === 'only') continue;

            return TRUE;
        }

        return FALSE;
    }

    private function criteriaKeywordHasNegators(string $keyword) : bool {
        // Some criteria like "did not sleep" or "didn't work" have negators in them.
        // We need to check for this cases to avoid returning an score like this: "not did not sleep"
        // If the criteria has a negator in itself, we will always return that the criteria
        // is not negated, since things like "not didn't sleep" or "not not going to come back"
        // are never going to bee seen in a real hotel review.
        foreach (DefaultAnalyzerConstants::NEGATORS as $negator)
            if (stripos($keyword, $negator) !== FALSE)
                return TRUE;
        
        return FALSE;
    }

    private function getEmphasizer(string $keyword, string $division) : ?\AppBundle\Entity\Emphasizer {
        foreach ($this->emphasizers as $possibleEmphasizer) {
            if (stripos($division, $possibleEmphasizer->getName() . ' ' . $keyword) === FALSE) continue;
            return $possibleEmphasizer;
        }
        return NULL;
    }

    private function getNegatedCriteriaScore(int $score) : int {
        // I don't think that negators should treat positive and negative criteria equally.
        // In my opinion, saying "not good" is clearly a negative thing, but saying "not bad" is not
        // necessarily a positive thing. Thus, I think there should be different score modifiers for
        // positive and negative crtieria.
        // Example: BAD criteria has -100 points. The modifier for the negative criteria is -0.1, so
        // saying "not bad" will result in a score of -100 * -0.1 = +10 points
        // Example 2: GOOD criteria has +100 points. The modifier for positive criteria is -1, so
        // saying "not good" will result in a score of 100 * -1 = -100 points.
        return round($score * (
            $score > 0 ? 
            DefaultAnalyzerConstants::NEGATED_POSITIVE_CRITERIA_SCORE_MODIFIER : 
            DefaultAnalyzerConstants::NEGATED_NEGATIVE_CRITERIA_SCORE_MODIFIER
        ));
    }

    private function pluralize(string $singularWord) : string {
        return Pluralizator::get()->pluralize($singularWord);
    }

    private function removeTopicsWithoutCriteria() : void {
        $topics = $this->AnalyzerResponse->getTopics();
        foreach ($topics as $topic)
            if (! count($this->AnalyzerResponse->getCriteria($topic)))
                $this->AnalyzerResponse->removeTopic($topic);
    }

    private function setCriteria() : DefaultAnalyzer {
        $this->criteria = $this->DoctrineManager->getRepository('AppBundle:Criteria')->findAll();
        return $this;
    }

    private function setTopics() : DefaultAnalyzer {
        $this->topics = $this->DoctrineManager->getRepository('AppBundle:Topic')->findBy([], ['priority' => 'DESC']);
        return $this;
    }

    private function setEmphasizers() : DefaultAnalyzer {
        $this->emphasizers = $this->DoctrineManager->getRepository('AppBundle:Emphasizer')->findAll();
        return $this;
    }

}