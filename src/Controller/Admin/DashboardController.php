<?php

namespace App\Controller\Admin;

use App\Entity\Avis;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard/admin', name: 'app_admin_dashboard')]
    public function index(): Response
    {
        return $this->render('admin/dashboard/admin.html.twig');
    }

    #[Route('/admin/dashboard/employe', name: 'app_employe_dashboard')]
    public function employe(EntityManagerInterface $em): Response
    {
        /* $comments = $em->getRepository(Avis::class)->getComments(); */
        
        $invalidComments = $em->getRepository(Avis::class)->invalidComments();
        $countInvalidComments = count($invalidComments);
        
        return $this->render('admin/dashboard/employe.html.twig',[
            'invalidComments'=>$invalidComments,
            'countInvalidComments'=>$countInvalidComments,
        ]);

    }
}
