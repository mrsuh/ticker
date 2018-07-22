<?php

namespace App\Controller\View;

use App\Entity\Ticker;
use App\Form\TickerForm;
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
            $project = $this->projectRepository->findOneRecent();
        }

        return $this->render('ticker/list.html.twig', [
                'projects' => $this->projectRepository->findAllWithTickers(),
                'tickers'  => $this->tickerRepository->findByProject($project)
            ]
        );
    }

    /**
     * @Route("/add", name="ticker.add", methods={"GET", "POST"})
     * @return Response
     */
    public function add(Request $request)
    {
        $form = $this->createForm(TickerForm::class, new Ticker());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $ticker = $form->getData();

                $this->tickerModel->create($ticker);

                return $this->redirectToRoute('ticker.list');

            } catch (\Exception $e) {
                $this->get('logger')->error($e->getMessage(), ['trace' => $e->getTraceAsString()]);
                $this->addFlash(Message::WARNING, 'Произошла ошибка');
            }
        }

        return $this->render(
            'ticker/add.html.twig',
            [
                'form' => $form->createView()
            ]);
    }
}