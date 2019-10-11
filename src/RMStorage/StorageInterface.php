<?php

namespace App\RMStorage;

interface StorageInterface
{
    /**
     * @return Project[]
     */
    public function getProjects(): array;

    /**
     * @return Issue[]
     */
    public function getIssues(): array;

    public function createTimeEntry(TimeEntry $timeEntry): bool;

    public function createIssue(Issue $issue): int;
}