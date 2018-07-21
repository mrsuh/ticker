<?php

namespace App\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Ticker;
use App\Repository\ProjectRepository;
use App\Repository\TickerRepository;
use App\RMStorage\Project;
use App\RMStorage\StorageInterface;

class SyncRMTasksCommand extends ContainerAwareCommand
{
    private $tickerRepository;
    private $projectRepository;
    private $storage;

    public function __construct($name = null, TickerRepository $tickerRepository, ProjectRepository $projectRepository, StorageInterface $storage)
    {
        parent::__construct($name);
        $this->tickerRepository  = $tickerRepository;
        $this->projectRepository = $projectRepository;
        $this->storage           = $storage;
    }

    protected function configure()
    {
        $this
            ->setName('app:sync')
            ->setDescription('Sync RM tasks');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
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