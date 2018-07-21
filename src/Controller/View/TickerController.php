<?php

namespace App\Controller\View;

use App\Repository\ProjectRepository;
use App\Repository\TickerRepository;
use App\RMStorage\StorageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/tickers")
 */
class TickerController extends Controller
{
    private $tickerRepository;
    private $projectRepository;
    private $storage;

    public function __construct(TickerRepository $tickerRepository, ProjectRepository $projectRepository, StorageInterface $storage)
    {
        $this->tickerRepository  = $tickerRepository;
        $this->projectRepository = $projectRepository;
        $this->storage           = $storage;
    }

    /**
     * @Route("/", name="ticker.list", methods={"GET"})
     * @return Response
     */
    public function list(Request $request)
    {
        $projectId = (int)$request->query->get('project');
        $project   = $this->projectRepository->findOneById($projectId);

        if (null === $project) {
            $project = $this->projectRepository->findOneRecent();//todo
        }

        return $this->render('ticker/list.html.twig', [
                'projects' => $this->projectRepository->findAllWithTickers(),
                'tickers'  => $this->tickerRepository->findByProject($project)
            ]
        );
    }

    /**
     * @Route("/add", name="ticker.add", methods={"GET"})
     * @return Response
     */
    public function add()
    {
        return $this->render('ticker/add.html.twig');
    }
}