<?php

namespace App\Model;

use App\Entity\Ticker;
use App\Entity\TimeLine;
use App\Repository\ProjectRepository;
use App\Repository\TimeLineRepository;
use App\Repository\TickerRepository;
use App\RMStorage\Issue;
use App\RMStorage\StorageInterface;
use App\RMStorage\TimeEntry;
use Doctrine\ORM\EntityManagerInterface;

class TickerModel
{
    private $em;
    private $timeLineRepository;
    private $tickerRepository;
    private $projectRepository;
    private $storage;

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

    public function create(Ticker $ticker)
    {
        $this->tickerRepository->create($ticker);
    }

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
}