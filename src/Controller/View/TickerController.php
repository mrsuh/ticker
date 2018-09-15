<?php

namespace App\Controller\View;

use App\Model\TickerModel;
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
    private $tickerModel;
    private $tickerRepository;
    private $projectRepository;
    private $storage;

    public function __construct(
        TickerModel $tickerModel,
        TickerRepository $tickerRepository,
        ProjectRepository $projectRepository,
        StorageInterface $storage
    )
    {
        $this->tickerModel       = $tickerModel;
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
            $recentProject = $this->projectRepository->findOneRecentWithTickers();

            return $this->redirectToRoute('ticker.list', ['project' => $recentProject->getId()]);
        }

        return $this->render('ticker/list.html.twig', [
                'projects' => $this->projectRepository->findAll(),
                'project'  => $project,
                'tickers'  => $this->tickerRepository->findByProject($project)
            ]
        );
    }
}