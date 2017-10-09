<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AnalysisCriteria
 *
 * @ORM\Table(name="analysis_criteria", uniqueConstraints={@ORM\UniqueConstraint(name="unique_analysis_criteria", columns={"id_analysis", "id_criteria", "id_emphasizer"})})))
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AnalysisCriteriaRepository")
 */
class AnalysisCriteria
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
     * @var Entity\Analysis
     *
     * @ORM\ManyToOne(targetEntity="Analysis", cascade={"persist"})
     * @ORM\JoinColumn(name="id_analysis", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $analysis;

    /**
     * @var Entity\Criteria
     *
     * @ORM\ManyToOne(targetEntity="Criteria")
     * @ORM\JoinColumn(name="id_criteria", referencedColumnName="id", nullable=false)
     */
    private $criteria;

    /**
     * @var Entity\Emphasizer
     *
     * @ORM\ManyToOne(targetEntity="Emphasizer")
     * @ORM\JoinColumn(name="id_emphasizer", referencedColumnName="id", nullable=true)
     */
    private $emphasizer;

    /**
     * @var bool
     *
     * @ORM\Column(name="negated", type="boolean")
     */
    private $negated;


    public function getId() : int {
        return $this->id;
    }

    public function setAnalysis(Analysis $analysis) : AnalysisCriteria {
        $this->analysis = $analysis;
        return $this;
    }

    public function getAnalysis() : Analysis {
        return $this->analysis;
    }

    public function setCriteria(Criteria $criteria) : AnalysisCriteria {
        $this->criteria = $criteria;
        return $this;
    }

    public function getCriteria() : Criteria {
        return $this->criteria;
    }

    public function setEmphasizer(?Emphasizer $emphasizer) : AnalysisCriteria {
        $this->emphasizer = $emphasizer;
        return $this;
    }

    public function getEmphasizer() : Emphasizer {
        return $this->emphasizer;
    }

    public function setNegated(bool $negated) : AnalysisCriteria {
        $this->negated = $negated;
        return $this;
    }

    public function getNegated() : bool {
        return $this->negated;
    }

}

