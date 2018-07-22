<?php

namespace App\Tests\Unit;

use App\Entity\Project;
use App\Entity\Ticker;
use App\Entity\TimeLine;
use App\Model\TickerModel;
use App\Repository\ProjectRepository;
use App\Repository\TickerRepository;
use App\Repository\TimeLineRepository;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TickerTest extends KernelTestCase
{
    /**
     * @var KernelInterface
     */
    protected static $kernel;

    /** @var  TickerModel */
    private $tickerModel;

    /** @var  TickerRepository */
    private $tickerRepository;

    /** @var  ProjectRepository */
    private $projectRepository;

    /** @var  TimeLineRepository */
    private $timeLineRepository;

    public static function initDb()
    {
        self::$kernel = self::bootKernel();

        $application = new Application(self::$kernel);

        $application->setAutoExit(false);

        $cmd = $application->find('doctrine:database:drop');
        $cmd->run(new ArrayInput(['--force' => true, '--if-exists' => true]), new NullOutput());

        $cmd = $application->find('doctrine:database:create');
        $cmd->run(new ArrayInput([]), new NullOutput());

        $cmd = $application->find('doctrine:schema:create');
        $cmd->run(new ArrayInput([]), new NullOutput());
    }

    public function setUp()
    {
        self::initDb();

        $kernel                   = self::bootKernel();
        $container                = $kernel->getContainer()->get('test.service_container');
        $this->tickerRepository   = $container->get('App\Repository\TickerRepository');
        $this->projectRepository  = $container->get('App\Repository\ProjectRepository');
        $this->timeLineRepository = $container->get('App\Repository\TimeLineRepository');

        $RMStorageMock = new RMStorageMock(
            $container->get('Psr\Log\LoggerInterface'),
            '',
            '',
            '',
            '',
            0,
            0
        );

        $this->tickerModel = new TickerModel(
            $container->get('Doctrine\ORM\EntityManagerInterface'),
            $container->get('App\Repository\TimeLineRepository'),
            $container->get('App\Repository\TickerRepository'),
            $container->get('App\Repository\ProjectRepository'),
            $RMStorageMock
        );
    }

    public function testTicker()
    {
        $project =
            (new Project())
                ->setName('proejct1')
                ->setRmId(1);
        $this->projectRepository->create($project);

        $ticker =
            (new Ticker())
                ->setRmId(1)
                ->setProject($project)
                ->setName('ticker');

        $this->tickerRepository->create($ticker);

        $this->tickerModel->tick($ticker);

        $this->assertTrue($ticker->isCurrent());
    }

    public function testChangeTicker()
    {
        $project =
            (new Project())
                ->setName('proejct2')
                ->setRmId(2);
        $this->projectRepository->create($project);

        $oldTimeLine = (new TimeLine());
        $this->timeLineRepository->create($oldTimeLine);

        $oldTicker =
            (new Ticker())
                ->setRmId(2)
                ->setProject($project)
                ->setName('old_ticker')
                ->setCurrent(true)
                ->setCurrentTimeLine($oldTimeLine);

        $this->tickerRepository->create($oldTicker);

        $oldTimeLine->setTicker($oldTicker);
        $this->timeLineRepository->update($oldTimeLine);

        $newTicker =
            (new Ticker())
                ->setRmId(3)
                ->setProject($project)
                ->setName('new_ticker');

        $this->tickerRepository->create($newTicker);

        $this->tickerModel->tick($newTicker);

        $this->assertTrue($newTicker->isCurrent());

        $this->assertNotEquals(null, $oldTimeLine->getFinishedAt());
    }
}