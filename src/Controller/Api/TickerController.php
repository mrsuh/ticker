<?php

namespace App\Controller\Api;

use App\Entity\Ticker;
use App\Model\TickerModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/tickers")
 */
class TickerController extends Controller
{
    private $tickerModel;

    public function __construct(TickerModel $tickerModel)
    {
        $this->tickerModel = $tickerModel;
    }

    /**
     * @Route("/{id}/tick", name="api.ticker.tick")
     * @Method({"PUT"})
     * @return Response
     */
    public function tick(Ticker $ticker)
    {
        $this->tickerModel->tick($ticker);

        return new JsonResponse(['status' => 'ok']);
    }
}