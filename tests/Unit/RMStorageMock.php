<?php

namespace App\Tests\Unit;

use App\RMStorage\Issue;
use App\RMStorage\Project;
use App\RMStorage\StorageInterface;
use App\RMStorage\TimeEntry;
use Psr\Log\LoggerInterface;

class RMStorageMock implements StorageInterface
{
    /**
     * Storage constructor.
     * @param LoggerInterface $logger
     * @param string          $rmUrl
     * @param string          $rmAuthUser
     * @param string          $rmAuthPass
     * @param string          $rmToken
     */
    public function __construct(
        LoggerInterface $logger,
        string $rmUrl,
        string $rmAuthUser,
        string $rmAuthPass,
        string $rmToken,
        int $rmTimeoutSec,
        int $rmConnTimeoutSec
    )
    {

    }

    /**
     * @return Project[]
     */
    public function getProjects(): array
    {
        return [];
    }

    /**
     * @return Issue[]
     */
    public function getIssues(): array
    {
        return [];
    }

    public function createTimeEntry(TimeEntry $timeEntry): bool
    {
        return true;
    }
}