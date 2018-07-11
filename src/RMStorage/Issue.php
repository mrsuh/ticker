<?php

namespace App\RMStorage;

class Issue
{

    private $id;

    private $subject;

    private $project;

    public function __construct(int $id, string $subject)
    {
        $this->id      = $id;
        $this->subject = $subject;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Issue
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return Project
     */
    public function getProject(): Project
    {
        return $this->project;
    }

    /**
     * @param Project $project
     * @return Issue
     */
    public function setProject(Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     * @return Issue
     */
    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }
}