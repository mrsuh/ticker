<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TickerRepository")
 */
class Ticker
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", unique=true, nullable=false)
     */
    private $rmId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Project")
     * @ORM\JoinColumn(name="project", referencedColumnName="id")
     */
    private $project;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $startedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastTickAt;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default": false})
     */
    private $current;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TimeLine")
     * @ORM\JoinColumn(name="timeline", referencedColumnName="id")
     */
    private $currentTimeLine;

    public function __construct()
    {
        $this->current    = false;
        $this->createdAt  = new \DateTime();
        $this->name       = '';
        $this->rmId       = null;
    }

    /**
     * @return mixed
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
     * @return Ticker
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
     * @return Ticker
     */
    public function setRmId(int $rmId): self
    {
        $this->rmId = $rmId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCategory(): int
    {
        return $this->category;
    }

    /**
     * @param int $category
     * @return Ticker
     */
    public function setCategory(int $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt():? \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return Ticker
     */
    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getStartedAt():? \DateTime
    {
        return $this->startedAt;
    }

    /**
     * @param \DateTime $startedAt
     * @return Ticker
     */
    public function setStartedAt(\DateTime $startedAt): self
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCurrent(): bool
    {
        return $this->current;
    }

    /**
     * @param bool $current
     * @return $this
     */
    public function setCurrent(bool $current): self
    {
        $this->current = $current;

        return $this;
    }

    public function getCurrentTimeLine():?TimeLine
    {
        return $this->currentTimeLine;
    }

    public function setCurrentTimeLine(TimeLine $currentTimeLine): self
    {
        $this->currentTimeLine = $currentTimeLine;

        return $this;
    }

    /**
     * @return Project|null
     */
    public function getProject():? Project
    {
        return $this->project;
    }

    /**
     * @param Project $project
     * @return Ticker
     */
    public function setProject(Project $project): self
    {
        $this->project = $project;

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
     * @return Ticker
     */
    public function setLastTickAt(\DateTime $lastTickAt): self
    {
        $this->lastTickAt = $lastTickAt;

        return $this;
    }
}
