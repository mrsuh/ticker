<?php

namespace App\Tests\Unit;

use App\Entity\Category;
use App\Entity\Ticker;
use App\Entity\TimeLine;
use App\Model\TickerModel;
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
        $this->timeLineRepository = $container->get('App\Repository\TimeLineRepository');
        $this->tickerModel        = $container->get('App\Model\TickerModel');
    }

    public function testTicker()
    {
        $ticker =
            (new Ticker())
                ->setName('ticker')
                ->setCategory(Category::OTHER);

        $this->tickerRepository->create($ticker);

        $this->tickerModel->tick($ticker);

        $this->assertTrue($ticker->isCurrent());
    }

    public function testChangeTicker()
    {
        $oldTimeLine = (new TimeLine());
        $this->timeLineRepository->create($oldTimeLine);

        $oldTicker =
            (new Ticker())
                ->setName('old_ticker')
                ->setCategory(Category::OTHER)
                ->setCurrent(true)
                ->setCurrentTimeLine($oldTimeLine);

        $this->tickerRepository->create($oldTicker);

        $oldTimeLine->setTicker($oldTicker);
        $this->timeLineRepository->update($oldTimeLine);

        $newTicker = (new Ticker())
            ->setName('new_ticker')
            ->setCategory(Category::OTHER);

        $this->tickerRepository->create($newTicker);

        $this->tickerModel->tick($newTicker);

        $this->assertTrue($newTicker->isCurrent());

        $this->assertNotEquals(null, $oldTimeLine->getFinishedAt());
    }
}