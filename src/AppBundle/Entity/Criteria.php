<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Criteria
 *
 * @ORM\Table(name="criteria", uniqueConstraints={@ORM\UniqueConstraint(name="unique_criteria", columns={"keyword"})})))
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CriteriaRepository")
 */
class Criteria
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
     * @var string
     *
     * @ORM\Column(name="keyword", type="string", length=70, unique=true)
     */
    private $keyword;

    /**
     * @var int
     *
     * @ORM\Column(name="score", type="integer")
     */
    private $score;


    public function getId() : int {
        return $this->id;
    }

    public function setKeyword(string $keyword) : Criteria {
        $this->keyword = $keyword;
        return $this;
    }

    public function getKeyword() : string {
        return $this->keyword;
    }

    public function setScore(int $score) : Criteria {
        $this->score = $score;
        return $this;
    }

    public function getScore() : int {
        return $this->score;
    }
}

