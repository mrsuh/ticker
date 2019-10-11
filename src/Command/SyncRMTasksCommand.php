<?php

namespace App\Command;

use App\Model\TickerModel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncRMTasksCommand extends Command
{
    private $tickerModel;

    public function __construct(TickerModel $tickerModel)
    {
        $this->tickerModel = $tickerModel;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:sync')
            ->setDescription('Sync RM tasks');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->tickerModel->sync();
    }
}