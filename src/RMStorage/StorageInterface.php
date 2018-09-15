<?php

namespace App\RMStorage;

use Psr\Log\LoggerInterface;

interface StorageInterface
{
    /**
     * StorageInterface constructor.
     * @param LoggerInterface $logger
     * @param string          $rmUrl
     * @param string          $rmAuthUser
     * @param string          $rmAuthPass
     * @param string          $rmToken
     * @param int             $rmId
     * @param int             $rmTimeoutSec
     * @param int             $rmConnTimeoutSec
     */
    public function __construct(
        LoggerInterface $logger,
        string $rmUrl,
        string $rmAuthUser,
        string $rmAuthPass,
        string $rmToken,
        int $rmId,
        int $rmTimeoutSec,
        int $rmConnTimeoutSec
    );

    /**
     * @return Project[]
     */
    public function getProjects(): array;

    /**
     * @return Issue[]
     */
    public function getIssues(): array;

    /**
     * @param TimeEntry $timeEntry
     * @return bool
     */
    public function createTimeEntry(TimeEntry $timeEntry): bool;

    /**
     * @param Issue $issue
     * @return int
     */
    public function createIssue(Issue $issue): int;
}