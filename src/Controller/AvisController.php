<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Covoiturage;
use App\Form\AvisFormType;
use App\Repository\AvisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class AvisController extends AbstractController{
    #[Route('/avis', name: 'app_avis')]

    public function index(AvisRepository $avisRepositpory): Response
    {
        $avis = $avisRepositpory->findall();

        return $this->render('avis/index.html.twig', [
            'avis'=>$avis,
        ]);
    }

    #[Route('/avis/new/{id}', name: 'app_avis_new', requirements: ['id' => '\d+'])]

    public function avisNew(int $id,Request $request, EntityManagerInterface $em,Security $security): Response
    {
        $utilisateur = $security->getUser();

       /*  $rate = $request->query->get('rate'); */

        if (!$utilisateur) {
            $this->addFlash('warning', 'Veuillez vous connecter ou créer un compte.');
            return $this->redirectToRoute('app_login');
        }

        $covoiturage = $em->getRepository(Covoiturage::class)->find($id);

        if (!$covoiturage) {
            throw $this->createNotFoundException('Covoiturage non trouvé.');
        }

        $conducteur = $covoiturage->getConducteur();

            $avis = new Avis();

            $form = $this->createForm(AvisFormType::class, $avis,);
        
            $form->handleRequest($request);
        
            if ($form->isSubmitted() && $form->isValid()) {
            
                $avis->setUtilisateur($conducteur);
                
               /*  $avis->addAvis($utilisateur); */
                
                $em->persist($avis);
                $em->flush();
            
                $this->addFlash('success', 'Votre avis est enregistré.');
                return $this->redirectToRoute('app_profil');
            }
            return $this->render('avis/new.html.twig', [
                'form' => $form->createView(),
                'conducteur'=> $conducteur,
               /*  'rate'=>$rateComments, */
            ]);
    }
}
