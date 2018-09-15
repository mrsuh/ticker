<?php

namespace App\Controller\Api;

use App\Entity\Project;
use App\Entity\Ticker;
use App\Model\TickerModel;
use App\Repository\TickerRepository;
use \Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
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

    public function __construct(TickerModel $tickerModel, TickerRepository $tickerRepository)
    {
        $this->tickerModel      = $tickerModel;
        $this->tickerRepository = $tickerRepository;
    }

    /**
     * @Route("/{id}/tick", name="api.ticker.tick", methods={"PUT"})
     * @return JsonResponse
     */
    public function tick(Ticker $ticker)
    {
        $this->tickerModel->tick($ticker);

        return new JsonResponse(['status' => 'ok']);
    }

    /**
     * @param Project $project
     * @param Request $request
     * @Route("/projects/{id}/tickers", name="api.ticker.create", methods={"POST"})
     * @return JsonResponse
     */
    public function create(Project $project, Request $request)
    {
        $name = $request->request->get('name');
        if (empty($request)) {
            return new JsonResponse(['status' => 'error'], Response::HTTP_BAD_REQUEST);
        }

        $ticker = (new Ticker())
            ->setProject($project)
            ->setName($name);
        $this->tickerModel->create($ticker);
        $this->tickerModel->tick($ticker);

        return new JsonResponse(['status' => 'ok', 'data' => [
            'id'         => $ticker->getId(),
            'rmId'       => $ticker->getRmId(),
            'name'       => $ticker->getName(),
            'lastTickAt' => $ticker->getLastTickAt()->getTimestamp(),
        ]]);
    }

    /**
     * @Route("/stop", name="api.ticker.stop", methods={"PUT"})
     * @return JsonResponse
     */
    public function stop()
    {
        $currentTicker = $this->tickerRepository->findOneCurrent();

        if (null !== $currentTicker) {
            $this->tickerModel->stop($currentTicker);
        }

        return new JsonResponse(['status' => 'ok']);
    }
}