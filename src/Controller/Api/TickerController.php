<?php

namespace App\Controller\Api;

use App\Entity\Ticker;
use App\Model\TickerModel;
use App\Repository\TickerRepository;
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
     * @return Response
     */
    public function tick(Ticker $ticker)
    {
        $this->tickerModel->tick($ticker);

        return new JsonResponse(['status' => 'ok']);
    }

    /**
     * @Route("/stop", name="api.ticker.stop", methods={"PUT"})
     * @return Response
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