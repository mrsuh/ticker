<?php

namespace App\RMStorage;

class TimeEntry
{

    private $issue;

    private $hours;

    private $seconds;

    public function __construct(Issue $issue, int $seconds)
    {
        $this->issue   = $issue;
        $this->seconds = $seconds;
        $this->hours   = round($seconds / 60 / 60, 8);
    }

    /**
     * @return int
     */
    public function getSeconds(): int
    {
        return $this->seconds;
    }

    /**
     * @return Issue
     */
    public function getIssue(): Issue
    {
        return $this->issue;
    }

    /**
     * @param Issue $issue
     * @return TimeEntry
     */
    public function setIssue(Issue $issue): self
    {
        $this->issue = $issue;

        return $this;
    }

    /**
     * @return float
     */
    public function getHours(): float
    {
        return $this->hours;
    }

    /**
     * @param float $hours
     * @return TimeEntry
     */
    public function setHours(float $hours): self
    {
        $this->hours = $hours;

        return $this;
    }
}