<?php

namespace App\DataFixtures\ORM;

use App\Entity\Ticker;
use App\Repository\TickerRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

class LoadTicker extends Fixture
{
    private $tickerRepository;
    private $fixturesTickerFile;

    public function __construct(TickerRepository $tickerRepository, string $fixturesTickerFile)
    {
        $this->tickerRepository   = $tickerRepository;
        $this->fixturesTickerFile = $fixturesTickerFile;
    }

    public function load(ObjectManager $manager)
    {
        $tasks = Yaml::parseFile($this->fixturesTickerFile);
        foreach ($tasks as $task) {
            $category = $task['category'];
            $name     = $task['name'];

            $this->tickerRepository->create(
                (new Ticker())
                    ->setCategory($category)
                    ->setName($name)
            );
        }
    }
}