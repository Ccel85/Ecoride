<?php

namespace App\Controller;

use App\Entity\Covoiturage;
use App\Form\CovoiturageFormType;
use App\Repository\VoitureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class CovoiturageController extends AbstractController
{
    #[Route('/covoiturage', name: 'app_covoiturage')]
    public function index(): Response
    {
        return $this->render('covoiturage/index.html.twig', [
            'controller_name' => 'CovoiturageController',
        ]);
    }

    //Création covoiturage
    #[Route('/covoiturage/new', name: 'app_covoiturage_new')]

    public function NewCovoiturage(Request $request,EntityManagerInterface $entityManager,Security $security): Response
    {
        $covoiturage = new Covoiturage();
        $form = $this->createForm(CovoiturageFormType::class, $covoiturage,[
            'user' => $this->getUser(),]); // Passer l'utilisateur connecté);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérez l'utilisateur connecté
        $utilisateur = $security->getUser();

        // Ajoutez l'utilisateur au covoiturage
        if ($utilisateur) {
            $covoiturage->addUtilisateur($utilisateur);
        }

            $entityManager->persist($covoiturage);
            $entityManager->flush();

            $this->addFlash('success', 'Covoiturage créé avec succès!');
            return $this->redirectToRoute('app_profil');
        }
        return $this->render('covoiturage/index.html.twig', [
            'form' => $form->createView(),
            'covoiturage' => $form,
        ]);
    }
    //Suppression Covoiturage
    #[Route('/covoiturage/{id}/remove', name: 'app_covoiturage_remove' , requirements: ['id' => '\d+']) ]

    public function RemoveCovoiturage(Security $security,Request $request,EntityManagerInterface $entityManager,Covoiturage $covoiturage): Response
    {
        $utilisateur = $security->getUser();

    if (!$utilisateur) {
        $this->addFlash('error', 'Utilisateur non connecté.');
        return $this->redirectToRoute('app_login');
    }
    // Vérifier si l'utilisateur est autorisé à supprimer ce covoiturage
    if (!$utilisateur->getCovoiturage()->contains($covoiturage)) {
        $this->addFlash('error', 'Vous n\'êtes pas autorisé à supprimer ce covoiturage.');
        return $this->redirectToRoute('app_profil');
    }
            // Supprimez le covoiturage de l\'utilisateur
        $utilisateur->removeCovoiturage($covoiturage);

        $entityManager->remove($covoiturage);

        // Sauvegardez les modifications
        $entityManager->flush();

        $this->addFlash('success', 'Covoiturage supprimé avec succès!');

        return $this->redirectToRoute('app_profil');

    }

    //Mise à jour des covoiturages proprietaire
    #[Route('/covoiturage/{id}/update', name: 'app_covoiturage_update', requirements: ['id' => '\d+'])]

    public function UpdateCovoiturage(Security $security,Request $request,EntityManagerInterface $entityManager,Covoiturage $covoiturage,VoitureRepository $voiture): Response
    {
        $utilisateur = $security->getUser();
        

    if (!$utilisateur) {
        $this->addFlash('error', 'Utilisateur non connecté.');
        return $this->redirectToRoute('app_login');
    }

    $voitures = $voiture->createQueryBuilder('v')
    ->where('v.utilisateur = :utilisateur')
    ->setParameter('utilisateur', $utilisateur)
    ->getQuery()
    ->getResult();

    // Vérifier si l'utilisateur est autorisé à modifier ce covoiturage
    if (!$utilisateur->getCovoiturage()->contains($covoiturage)) {
        $this->addFlash('error', 'Vous n\'êtes pas autorisé à modifier ce covoiturage.');
        return $this->redirectToRoute('app_profil');
    }

    $form = $this->createForm(CovoiturageFormType::class, $covoiturage,[
        'voitures' => $voitures, // Liste des voitures récupérées
    ]);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();

        $this->addFlash('success', 'Covoiturage mis à jour avec succès.');
        return $this->redirectToRoute('app_profil'); // Redirection après succès
    }
        return $this->render('covoiturage/index.html.twig', [
            'covoiturage' => $covoiturage,
            'form' => $form->createView(),
            'voitures' => $voitures
        ]);
    }
}