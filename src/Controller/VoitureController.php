<?php

namespace App\Controller;

use App\Entity\Voiture;
use App\Form\VoitureFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class VoitureController extends AbstractController
{
#[Route('/voiture', name: 'app_voiture')]
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

    public function NewVoiture(Request $request, EntityManagerInterface $entityManager,Security $security): Response
    {
        $utilisateur = $security->getUser();

        if ($utilisateur) {

        $voiture = new voiture();
        $form = $this->createForm(VoitureFormType::class, $voiture,[
            'utilisateur' => $utilisateur, // Passer l'utilisateur connecté);
            'voitures' => $voiture
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Récupérez l'utilisateur connecté
        $utilisateur = $security->getUser();

        // Ajoutez l'utilisateur à la voiture
        if ($utilisateur) {
            $utilisateur->addVoiture($voiture);
        }

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

    public function RemoveVoiture(Security $security,Request $request,EntityManagerInterface $entityManager,Voiture $voiture): Response
    {
        $utilisateur = $security->getUser();

    if (!$utilisateur) {
        $this->addFlash('error', 'Utilisateur non connecté.');
        return $this->redirectToRoute('app_login');
    }
    // Vérifier si l'utilisateur est autorisé à supprimer ce covoiturage
    if (!$utilisateur->getVoiture()->contains($voiture)) {
        $this->addFlash('error', 'Vous n\'êtes pas autorisé à supprimer ce covoiturage.');
        return $this->redirectToRoute('app_profil');
    }
    if ($voiture->getCovoiturages()->count() > 0) {
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
