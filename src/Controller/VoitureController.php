<?php

namespace App\Controller;

use App\Entity\Voiture;
use App\Form\VoitureFormType;
use App\Document\CovoiturageMongo;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class VoitureController extends AbstractController
{
    #[Route('/voiture', name: 'app_voiture' )]
        public function index(): Response
        {
            return $this->render('voiture/index.html.twig', [
                'controller_name' => 'VoitureController',
            ]);
        }

    #[Route('/voiture/{id}/update', name: 'app_voiture_update')]
        public function update(int $id, EntityManagerInterface $em,Request $request): Response
        {
            $voiture = $em->getRepository(Voiture::class)->find($id);

            // Récupérer l'utilisateur lié à la voiture
            $utilisateur = $voiture->getUtilisateur();

            if (!$voiture) {
                throw $this->createAccessDeniedException('Vous n\'avez pas de véhicule d\'enregistrer');
            }

            //création form
            $form = $this->createForm(VoitureFormType::class,$voiture);

            // Gérer la soumission du formulaire
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // Persister les modifications
                $em->flush();

                $this->addFlash('success', 'Le véhicule a été modifié');
            
                // Rediriger après la sauvegarde
                return $this->redirectToRoute('app_profil', ['id' => $utilisateur->getId()]);
            }

            return $this->render('voiture/new.html.twig', [
                'form' => $form->createView(),
                'utilisateur' => $utilisateur,
                'voiture' => $voiture,
            ]);
        }


    #[Route('/voiture/new', name: 'app_voiture_new')]

        public function NewVoiture(
            Request $request, 
            EntityManagerInterface $entityManager,
            Security $security): Response
        {
            $utilisateur = $security->getUser();

            if ($utilisateur) {

                $voiture = new voiture();
                $form = $this->createForm(VoitureFormType::class, $voiture,[
                    'utilisateur' => $utilisateur, // Passer l'utilisateur connecté;
                    'voitures' => $voiture
                ]);

                $form->handleRequest($request);
            
                if ($form->isSubmitted() && $form->isValid()) {
                    //Récupérez l'utilisateur connecté
                    $utilisateur = $security->getUser();
                    $utilisateur->addVoiture($voiture);
                    

                    $entityManager->persist($voiture);
                    $entityManager->flush();

                    $this->addFlash('success', 'Le véhicule a été créé avec succès!');
                    return $this->redirectToRoute('app_profil');
                    }
            
                return $this->render('voiture/new.html.twig', [
                    'form' => $form->createView(),
                    'utilisateur' => $utilisateur,
                    'voiture' => $voiture,
                ]);
        } else {
            $this->addFlash('warning', 'Veuillez vous connecter ou créer un compte.');
            return $this->redirectToRoute('app_login');
        }
    }

        //Suppression voiture
    #[Route('/voiture/{id}/remove', name: 'app_voiture_remove' , requirements: ['id' => '\d+']) ]

    public function RemoveVoiture(
        int $id,
        Security $security,
        EntityManagerInterface $entityManager,
        DocumentManager $dm,
        ): Response
    {
        $utilisateur = $security->getUser();
        $voiture = $entityManager->getRepository(Voiture::class)->find($id);
    
        if (!$utilisateur) {
            $this->addFlash('error', 'Utilisateur non connecté.');
            return $this->redirectToRoute('app_login');
        }
        if (!$voiture) {
            $this->addFlash('warning', 'Le véhicule n\'existe pas !');
            return $this->redirectToRoute('app_profil');
        }

        // Vérifier si l'utilisateur est autorisé à supprimer cette voiture
        if (!$utilisateur->getVoiture()->contains($voiture)) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à supprimer ce véhicule.');
            return $this->redirectToRoute('app_profil');
        }

        $voitureId = $voiture->getId();
        dump($voitureId);
        $voitureId = $dm->getRepository(CovoiturageMongo::class)->findBy(['voitureId' => $voitureId]);
        dump($voitureId);

        if ($voitureId) {
            $this->addFlash('warning', 'Cette voiture est associée à des covoiturages et ne peut pas être supprimée,veullez alors modifier de véhicule dans la modification de covoiturage.');
            return $this->redirectToRoute('app_profil');
        }
        // Supprimez la voiture de l\'utilisateur
            $utilisateur->removeVoiture($voiture);

            $entityManager->remove($voiture);

            // Sauvegardez les modifications
            $entityManager->flush();

            $this->addFlash('success', 'Le véhicule a été supprimé avec succès!');

            return $this->redirectToRoute('app_profil');

    }
}
