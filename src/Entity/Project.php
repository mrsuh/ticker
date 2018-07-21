<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProjectRepository")
 */
class Project
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", unique=true)
     */
    private $rmId;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastTickAt;

    public function __construct()
    {
        $this->name = '';
        $this->rmId = null;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Project
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return int
     */
    public function getRmId():? int
    {
        return $this->rmId;
    }

    /**
     * @param int $rmId
     * @return Project
     */
    public function setRmId(int $rmId): self
    {
        $this->rmId = $rmId;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastTickAt():? \DateTime
    {
        return $this->lastTickAt;
    }

    /**
     * @param \DateTime $lastTickAt
     * @return Project
     */
    public function setLastTickAt(\DateTime $lastTickAt): self
    {
        $this->lastTickAt = $lastTickAt;

        return $this;
    }
}
