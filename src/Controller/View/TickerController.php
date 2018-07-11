<?php

namespace App\Controller\View;

use App\Entity\Category;
use App\Entity\Ticker;
use App\Repository\ProjectRepository;
use App\Repository\TickerRepository;
use App\RMStorage\Project;
use App\RMStorage\StorageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
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
     * @Route("/", name="ticker.list")
     * @Method({"GET"})
     * @return Response
     */
    public function list(Request $request)
    {
        $category = (int)$request->query->get('category');
        if (0 === $category) {
            $category = Category::WORK;
        }

        if ($category === Category::WORK) {
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
                    ->setCategory(Category::WORK)
                    ->setRmId($issue->getId())
                    ->setName($issue->getSubject());

                $this->tickerRepository->create(
                    $rmTicker
                );

                $tickers[] = $rmTicker;
            }
        }


        return $this->render('ticker/list.html.twig', [
                'categories' => [Category::WORK, Category::REST, Category::HOBBY, Category::OTHER],
                'tickers'    => $this->tickerRepository->findByCategory($category)]
        );
    }

    /**
     * @Route("/add", name="ticker.add")
     * @Method({"GET"})
     * @return Response
     */
    public function add()
    {
        return $this->render('ticker/add.html.twig');
    }
}