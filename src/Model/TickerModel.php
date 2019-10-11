<?php

namespace App\Model;

use App\Entity\Ticker;
use App\Entity\TimeLine;
use App\Repository\ProjectRepository;
use App\Repository\TickerRepository;
use App\Repository\TimeLineRepository;
use App\RMStorage\Issue;
use App\RMStorage\Project;
use App\RMStorage\StorageInterface;
use App\RMStorage\TimeEntry;
use Doctrine\ORM\EntityManagerInterface;

class TickerModel
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var TimeLineRepository
     */
    private $timeLineRepository;
    /**
     * @var TickerRepository
     */
    private $tickerRepository;
    /**
     * @var ProjectRepository
     */
    private $projectRepository;
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * TickerModel constructor.
     * @param EntityManagerInterface $em
     * @param TimeLineRepository     $timeLineRepository
     * @param TickerRepository       $tickerRepository
     * @param ProjectRepository      $projectRepository
     * @param StorageInterface       $storage
     */
    public function __construct(
        EntityManagerInterface $em,
        TimeLineRepository $timeLineRepository,
        TickerRepository $tickerRepository,
        ProjectRepository $projectRepository,
        StorageInterface $storage
    )
    {
        $this->em                 = $em;
        $this->timeLineRepository = $timeLineRepository;
        $this->tickerRepository   = $tickerRepository;
        $this->projectRepository  = $projectRepository;
        $this->storage            = $storage;
    }

    /**
     * @param Ticker $ticker
     * @throws \Exception
     */
    public function create(Ticker $ticker)
    {
        if (null === $ticker->getRmId()) {
            $project = (new Project($ticker->getProject()->getRmId(), $ticker->getProject()->getName()));
            $issue   = (new Issue(0, $ticker->getName()))->setProject($project);

            $issueId = $this->storage->createIssue($issue);

            if (0 === $issueId) {
                throw new \Exception('Invalid create issue request');
            }

            $ticker->setRmId($issueId);
        }

        $this->tickerRepository->create($ticker);
    }

    /**
     * @param Ticker $ticker
     * @return Ticker
     * @throws \Exception
     */
    public function tick(Ticker $ticker)
    {
        $this->em->beginTransaction();

        try {

            $currentTicker = $this->tickerRepository->findOneCurrent();

            if (null !== $currentTicker) {
                $this->stop($currentTicker);
            }

            $this->tickerRepository->clearCurrent();

            $newTimeLine = new TimeLine();
            $newTimeLine->setTicker($ticker);
            $this->timeLineRepository->create($newTimeLine);

            $ticker
                ->setStartedAt(new \DateTime())
                ->setLastTickAt(new \DateTime())
                ->setCurrentTimeline($newTimeLine)
                ->setCurrent(true);
            $this->tickerRepository->update($ticker);

            $project = $ticker->getProject();
            $project->setLastTickAt(new \DateTime());
            $this->projectRepository->update($project);

            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();

            throw $e;
        }

        return $ticker;
    }

    /**
     * @param Ticker $ticker
     * @return bool
     */
    public function stop(Ticker $ticker)
    {
        if (!$ticker->isCurrent()) {
            return false;
        }

        $ticker->setCurrent(false);
        $this->tickerRepository->update($ticker);

        $timeLine = $ticker->getCurrentTimeLine();

        if (null === $timeLine) {
            return false;
        }

        if (null !== $timeLine->getFinishedAt()) {
            return false;
        }

        $timeLine->setFinishedAt(new \DateTime());//todo separate timeline to parts for days
        $this->timeLineRepository->update($timeLine);

        $timeEntry = (new TimeEntry(
            new Issue($ticker->getRmId(), $ticker->getName()),
            $timeLine->getDuration())
        );

        $this->storage->createTimeEntry($timeEntry);
    }

    public function sync(): void
    {
        $rmProjects = [];
        foreach ($this->projectRepository->findAll() as $project) {
            if (null === $project->getRmId()) {
                continue;
            }

            $rmProjects[$project->getRmId()] = $project;
        }

        foreach ($this->storage->getProjects() as $project) {
            if (array_key_exists($project->getId(), $rmProjects)) {
                /** @var $rmProject  Project */
                $rmProject = $rmProjects[$project->getId()];
                if ($project->getName() !== $rmProject->getName()) {
                    $rmProject->setName($project->getName());

                    $this->projectRepository->update($rmProject);
                }

                continue;
            }

            $rmProject =
                (new \App\Entity\Project())
                    ->setRmId($project->getId())
                    ->setName($project->getName());

            $this->projectRepository->create($rmProject);

            $rmProjects[$rmProject->getRmId()] = $rmProject;
        }

        $rmTickers = [];
        foreach ($this->tickerRepository->findAll() as $ticker) {
            if (null === $ticker->getRmId()) {
                continue;
            }
            $rmTickers[$ticker->getRmId()] = $ticker;
        }

        foreach ($this->storage->getIssues() as $issue) {
            if (array_key_exists($issue->getId(), $rmTickers)) {
                /** @var $rmTicker Ticker */
                $rmTicker = $rmTickers[$issue->getId()];

                if ($rmTicker->getName() !== $issue->getSubject()) {
                    $rmTicker->setName($issue->getSubject());
                    $this->tickerRepository->update($rmTicker);
                }

                continue;
            }

            if (!array_key_exists($issue->getProject()->getId(), $rmProjects)) {
                continue;
            }

            /** @var $rmProject \App\Entity\Project */
            $rmProject = $rmProjects[$issue->getProject()->getId()];

            $rmTicker = (new Ticker())
                ->setProject($rmProject)
                ->setRmId($issue->getId())
                ->setName($issue->getSubject());

            $this->tickerRepository->create(
                $rmTicker
            );
        }
    }
}