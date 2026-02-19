<?php

namespace App\Controller;

use App\Repository\AvisRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        AvisRepository $avisRepository
        ):Response
    {
        $avis = $avisRepository -> validComments();
        return $this->render('home/index.html.twig', [
            'avis'=> $avis,
            /* 'comment'=> $comment */
        ]);
    }
}
