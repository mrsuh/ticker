<?php

namespace App\Model;

use App\Entity\Ticker;
use App\Entity\TimeLine;
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
    private $storage;

    public function __construct(
        EntityManagerInterface $em,
        TimeLineRepository $timeLineRepository,
        TickerRepository $tickerRepository,
        StorageInterface $storage
    )
    {
        $this->em                 = $em;
        $this->timeLineRepository = $timeLineRepository;
        $this->tickerRepository   = $tickerRepository;
        $this->storage            = $storage;
    }

    public function tick(Ticker $ticker)
    {
        $this->em->beginTransaction();

        try {

            $currentTicker = $this->tickerRepository->findOneCurrent();

            if (null !== $currentTicker) {
                $currentTicker->setCurrent(false);
                $this->tickerRepository->update($ticker);

                $currentTimeLine = $currentTicker->getCurrentTimeLine();
                if (null !== $currentTimeLine) {
                    $currentTimeLine->setFinishedAt(new \DateTime());//todo math duration
                    //todo separate timeline to parts for days
                    $this->timeLineRepository->update($currentTimeLine);


                    if (null !== $currentTicker->getRmId()) {

                        $seconds = $currentTimeLine->getDuration();
                        $minutes = ceil($seconds / 60);

                        $timeEntry = (new TimeEntry(new Issue($currentTicker->getRmId(), $currentTicker->getName()), sprintf('%dm', $minutes)));

                        $this->storage->createTimeEntry($timeEntry);
                    }
                }
            }

            $this->tickerRepository->clearCurrent();

            $newTimeLine = new TimeLine();
            $newTimeLine->setTicker($ticker);
            $this->timeLineRepository->create($newTimeLine);

            $ticker
                ->setStartedAt(new \DateTime())
                ->setLastTickAt(new \DateTime())
                ->setUsageCount($ticker->getUsageCount() + 1)//todo
                ->setCurrentTimeline($newTimeLine)
                ->setCurrent(true);
            $this->tickerRepository->update($ticker);

            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();

            throw $e;
        }

        return $ticker;
    }
}