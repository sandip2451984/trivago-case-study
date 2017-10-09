<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

/**
 * Analysis
 *
 * @ORM\Table(name="analysis", uniqueConstraints={@ORM\UniqueConstraint(name="unique_analysis", columns={"id_review", "id_topic"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AnalysisRepository")
 */
class Analysis
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Entity\Review
     *
     * @ORM\ManyToOne(targetEntity="Review", cascade={"persist"})
     * @ORM\JoinColumn(name="id_review", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $review;

    /**
     * @var Entity\Topic
     *
     * @ORM\ManyToOne(targetEntity="Topic")
     * @ORM\JoinColumn(name="id_topic", referencedColumnName="id", nullable=false)
     */
    private $topic;

    /**
     * @var int
     *
     * @ORM\Column(name="score", type="integer")
     */
    private $score;

    /**
    * @var PersistentCollection
    *
    * @ORM\OneToMany(targetEntity="AnalysisCriteria", mappedBy="analysis")
    */
    private $analysisCriteria;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    public function __construct() {
        $this->createdAt = new \DateTime();
    }

    public function getId() : int {
        return $this->id;
    }

    public function setReview(Review $review) : Analysis {
        $this->review = $review;
        return $this;
    }

    public function getReview() : Review {
        return $this->review;
    }

    public function setTopic(Topic $topic) : Analysis {
        $this->topic = $topic;
        return $this;
    }

    public function getTopic() : Topic {
        return $this->topic;
    }

    public function setScore(int $score) : Analysis {
        $this->score = $score;
        return $this;
    }

    public function getScore() : int {
        return $this->score;
    }

    public function setCreatedAt(\Datetime $createdAt) : Analysis {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getCreatedAt() : \Datetime {
        return $this->createdAt;
    }

    public function setAnalysisCriteria(PersistentCollection $analysisCriteria) : Analysis {
        $this->analysisCriteria = $analysisCriteria;
        return $this;
    }

    public function getAnalysisCriteria() : PersistentCollection {
        return $this->analysisCriteria;
    }
}

