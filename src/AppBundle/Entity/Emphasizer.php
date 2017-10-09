<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Topic
 *
 * @ORM\Table(name="emphasizers", uniqueConstraints={@ORM\UniqueConstraint(name="unique_emphasizer", columns={"name"})})))
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EmphasizerRepository")
 */
class Emphasizer
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
     * @ORM\Column(name="name", type="string", length=30, unique=true)
     */
    private $name;

    /**
     * @var float
     *
     * @ORM\Column(name="score_modifier", type="decimal", nullable=false)
     */
    private $scoreModifier = 0;


    public function getId() : int {
        return $this->id;
    }

    public function setName(string $name) : Emphasizer {
        $this->name = $name;
        return $this;
    }

    public function getName() : string {
        return $this->name;
    }

    public function setScoreModifier(float $scoreModifier) : Emphasizer {
        $this->scoreModifier = $scoreModifier;
        return $this;
    }

    public function getScoreModifier() : float {
        return $this->scoreModifier;
    }
}

