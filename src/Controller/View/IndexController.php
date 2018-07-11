<?php

namespace App\Controller\View;

use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class IndexController extends Controller
{
    /**
     * @Route("/", name="app.index")
     * @Method({"GET"})
     */
    public function index()
    {
        return $this->redirectToRoute('ticker.list');
    }
}