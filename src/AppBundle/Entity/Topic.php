<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

/**
 * Topic
 *
 * @ORM\Table(name="topics", uniqueConstraints={@ORM\UniqueConstraint(name="unqiue_topic", columns={"name"})})))
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TopicRepository")
 */
class Topic
{

    const UNKNOWN_TOPIC_NAME = "unknown";

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
     * @var int
     *
     * @ORM\Column(name="priority", type="integer", nullable=false)
     */
    private $priority = 0;


    /**
    * @var PersistentCollection
    *
    * @ORM\OneToMany(targetEntity="TopicAlias", mappedBy="topic")
    */
    private $aliases;


    public function getAliases() : PersistentCollection {
        return $this->aliases;
    }

    public function getId() : int {
        return $this->id;
    }

    public function setName(string $name) : Topic {
        $this->name = $name;
        return $this;
    }

    public function getName() : string {
        return $this->name;
    }

    public function setPriority(int $priority) : Topic {
        $this->priority = $priority;
        return $this;
    }

    public function getPriority() : int {
        return $this->priority;
    }
}

