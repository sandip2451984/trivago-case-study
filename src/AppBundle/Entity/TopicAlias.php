<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TopicAlias
 *
 * @ORM\Table(name="topics_aliases", uniqueConstraints={@ORM\UniqueConstraint(name="unique_topic_alias", columns={"alias"})})))
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TopicAliasRepository")
 */
class TopicAlias
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
     * @var Entity\Topic
     *
     * @ORM\ManyToOne(targetEntity="Topic")
     * @ORM\JoinColumn(name="id_topic", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $topic;

    /**
     * @var string
     *
     * @ORM\Column(name="alias", type="string", length=30, unique=true)
     */
    private $alias;


    public function getId() : int {
        return $this->id;
    }

    public function setTopic(Topic $topic) : TopicAlias {
        $this->topic = $topic;
        return $this;
    }

    public function getTopic() : Topic {
        return $this->topic;
    }

    public function setAlias(string $alias) : TopicAlias {
        $this->alias = $alias;
        return $this;
    }

    public function getAlias() : string {
        return $this->alias;
    }
}

