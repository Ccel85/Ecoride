<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Voiture;
use App\Entity\Covoiturage;
use Doctrine\ORM\EntityManager;
use App\Form\CovoiturageFormType;
use App\Repository\VoitureRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CovoiturageRepository;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class CovoiturageController extends AbstractController
{
    #[Route('/covoiturage', name: 'app_covoiturage')]
    public function index(CovoiturageRepository $repository,VoitureRepository $voituresRepository): Response
    {
        $covoiturages = $repository->findAll();
        $voitures = $voituresRepository->findAll();

        if (!$covoiturages) {
            throw $this->createNotFoundException("Il n'y a pas de covoiturage à afficher");
        }
        
        return $this->render('covoiturage/index.html.twig', [
            'covoiturages'=>$covoiturages,
            'voitures'=>$voitures,
        ]);
    }

    #[Route('/covoiturage/{id}', name: 'app_covoiturage_id', requirements: ['id' => '\d+'])]
    public function détail(EntityManagerInterface $em,int $id): Response
    {
        //on recupere le covoiturage selon son ID
        $covoiturage = $em->getRepository(Covoiturage::class)->find($id);

        if (!$covoiturage) {
            throw $this->createNotFoundException("Le covoiturage avec l'ID {$id} n'existe pas.");
        }
        // Récupération des informations liées
        $conducteur = $covoiturage->getConducteur();//conducteur lié au covoiturage;
        $commentsUser = $em->getRepository(Avis::class)->findBy(['utilisateur' => $conducteur]);
        $voitures = $em->getRepository(Voiture::class)->findBy(['utilisateur' => $conducteur]);
        $observations = $conducteur->getObservation();

         // Scinder le texte par les virgules
        $observationExplode = explode(',' ,$observations);

        return $this->render('covoiturage/covoiturageDetail.html.twig', [
            'covoiturage'=>$covoiturage,
            'conducteur'=>$conducteur,
            'commentaires'=>$commentsUser,
            'voitures'=>$voitures,
            'observations'=>$observationExplode,

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
        return $this->render('covoiturage/form.html.twig', [
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
        return $this->render('covoiturage/form.html.twig', [
            'covoiturage' => $covoiturage,
            'form' => $form->createView(),
            'voitures' => $voitures
        ]);
    }

    #[Route('/covoiturageRecherche', name: 'app_covoiturage_recherche', methods: ['GET'])]
    public function covoiturageRecherche(CovoiturageRepository $repository, Request $request): Response
    {
        // Récupérer les données du formulaire
        $dateDepart = $request->query->get('date');
        $lieuDepart = $request->query->get('depart');
        $lieuArrivee = $request->query->get('arrivee');
        $placeDispo = $request->query->get('placeDispo');
        
        // Construction la requête pour filtrer les covoiturages
        $queryBuilder = $repository->createQueryBuilder('c');

        if ($dateDepart){
            $queryBuilder->andWhere('c.dateDepart = :dateDepart')
            ->setParameter('dateDepart', $dateDepart );
        }
        if ($lieuDepart){
            $queryBuilder->andWhere('c.lieuDepart LIKE :lieuDepart')
            ->setParameter('lieuDepart', '%' . $lieuDepart . '%');
        }
        if ($lieuArrivee){
            $queryBuilder->andWhere('c.lieuArrivee LIKE :lieuArrivee')
            ->setParameter('lieuArrivee', '%' . $lieuArrivee . '%');
        }
        if ($placeDispo){
            $queryBuilder->andWhere('c.placeDispo >= :placeDispo')
            ->setParameter('placeDispo',$placeDispo);
        }

        $covoiturages = $queryBuilder->getQuery()->getResult();

        

        if (!$covoiturages) {
            $this->addFlash('warning', 'Il n\'y a pas de covoiturage avec ces critères.');
        }

        /*  $covoiturages = $repository->findAll();
        $form = $this->createForm(CovoiturageRepository::class, $covoiturages);
        $form->handleRequest($request); 
        $date=$form->get('dateDepart')->getData();
        $depart=$form->get('LieuDepart')->getData();
        $arrivee=$form->get('Lieuarrivee')->getData();
        $placeDispo=$form->get('placeDispo')->getData(); */

        /* if (!$covoiturages) {
            throw $this->createNotFoundException("Il n'y a pas de covoiturage à afficher");
        } */
        
        //$covoiturageResult=$repository-> findBySearch($date,$depart,$arrivee,$placeDispo);
        
        return $this->render('covoiturage/index.html.twig', [
            'covoiturages'=>$covoiturages,
            
        ]);
    }
}