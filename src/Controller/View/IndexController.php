<?php

namespace App\Controller\View;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class IndexController extends Controller
{
    /**
     * @Route("/", name="app.index", methods={"GET"})
     */
    public function index()
    {
        return $this->redirectToRoute('ticker.list');
    }
}