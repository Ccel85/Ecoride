<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Voiture;
use App\Entity\Covoiturage;
use App\Entity\Utilisateur;
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
    //lister les covoiturages
    #[Route('/covoiturage', name: 'app_covoiturage')]
    public function index(CovoiturageRepository $repository,VoitureRepository $voituresRepository): Response
    {
        $covoiturages = $repository->findAll();
        $voitures = $voituresRepository->findAll();
        
        return $this->render('covoiturage/index.html.twig', [
            'covoiturages'=>$covoiturages,
            'voitures'=>$voitures,
        ]);
    }
    // voir un covoiturage selon son Id
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
    
    public function NewCovoiturage(Request $request, EntityManagerInterface $entityManager, Security $security, VoitureRepository $voitureRepository,UtilisateurRepository $utilisateurRepository): Response
    {
            $utilisateur = $security->getUser();
            // Récupérer les voitures associées à l'utilisateur
            $voitures = $voitureRepository->findBy(['utilisateur' => $utilisateur]);
            
            if (!$utilisateur) {
                $this->addFlash('warning', 'Veuillez vous connecter ou créer un compte.');
                return $this->redirectToRoute('app_login');
            }
            $credits = $utilisateur->getCredits();
            
            if ($credits < 2){
                $this->addFlash('warning','Vous n\'avez pas assez de crédits pour créér un covoiturage');
                return $this->redirectToRoute('app_profil');
            }
            
            if(!$voitures) {
                $this->addFlash('warning', 'Veuillez tout d\'abord enregistrer votre véhicule. ');
                return $this->redirectToRoute('app_voiture_new');
            }
            
            $majCredit =$credits- 2;
            $covoiturage = new Covoiturage();
            $form = $this->createForm(CovoiturageFormType::class, $covoiturage,[
                'utilisateur' => $utilisateur, // Passer l'utilisateur connecté);
                'voitures' => $voitures // Passe les voitures au formulaire
            ]);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Récupérez l'utilisateur connecté
            $utilisateur = $security->getUser();

            // Associer le conducteur automatiquement
            $covoiturage->setConducteur($utilisateur);

            // Ajoutez l'utilisateur au covoiturage
            if ($utilisateur) {
                $covoiturage->addUtilisateur($utilisateur);
            }
            // Mettre à jour l'entité Utilisateur
                $utilisateur->setCredits($majCredit);

                $entityManager->persist($covoiturage);
                $entityManager->persist($utilisateur);
                $entityManager->flush();

                $this->addFlash('success', 'Le covoiturage a été créé avec succès!');
                return $this->redirectToRoute('app_profil');
            }
            return $this->render('covoiturage/new.html.twig', [
                'form' => $form->createView(),
                'utilisateur' => $utilisateur,
                'voitures' => $voitures,
            ]);

    }

    //Suppression Covoiturage
    #[Route('/covoiturage/{id}/remove', name: 'app_covoiturage_remove' , requirements: ['id' => '\d+']) ]

    public function RemoveCovoiturage(Security $security,Request $request,EntityManagerInterface $entityManager,Covoiturage $covoiturage): Response
    {
        $utilisateur = $security->getUser();
        $credits = $utilisateur->getCredits();

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

        $majCredits =$credits + 2;
        
        $utilisateur->setCredits($majCredits);
        $utilisateur->removeCovoiturage($covoiturage);

        $entityManager->remove($covoiturage);


        // Sauvegardez les modifications
        $entityManager->flush();

        $this->addFlash('success', 'Covoiturage supprimé avec succès!');

        return $this->redirectToRoute('app_profil');

    }

    //Mise à jour des covoiturages proprietaire
    #[Route('/covoiturage/{id}/update', name: 'app_covoiturage_update', requirements: ['id' => '\d+'])]

    public function UpdateCovoiturage(Security $security,Request $request,EntityManagerInterface $entityManager,Covoiturage $covoiturage,CovoiturageRepository $repository,VoitureRepository $voiture): Response
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
            return $this->render('covoiturage/new.html.twig', [
                'covoiturage' => $covoiturage,
                'form' => $form->createView(),
                'voitures' => $voitures
            ]);
    }

    //Recherche covoiturage
    #[Route('/covoiturageRecherche', name: 'app_covoiturage_recherche', methods: ['GET'])]
    public function covoiturageRecherche(CovoiturageRepository $repository, Request $request): Response
    {
        // Récupérer les données du formulaire
        $dateDepart = $request->query->get('date');
        $lieuDepart = $request->query->get('depart');
        $lieuArrivee = $request->query->get('arrivee');
        $prix = $request->query->get('prix');
        
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
        if ($prix){
            $queryBuilder->andWhere('c.prix <= :prix')
            ->setParameter('prix',$prix);
        }
        $covoiturages = $queryBuilder->getQuery()->getResult();

        //Apparaitre que les covoiturages futur
        if ($covoiturages !== null) {
            foreach ($covoiturages as $covoiturage) {
                $now = new \DateTime();
                $dateFuture = $covoiturage->getDateDepart() > $now;
                $covoiturage->dateFuture = $dateFuture;  // Ajoute la propriété `dateFuture`
            }
        }
        
        return $this->render('covoiturage/index.html.twig', [
            'covoiturages'=>$covoiturages,
            
        ]);
    }

    //validation participation covoiturage
    #[Route('/covoiturage/{id}/participate', name: 'app_covoiturage_participate', requirements: ['id'=>'\d+'])]

    public function participate(Security $security,EntityManagerInterface $entityManager,Covoiturage $covoiturages) : Response
    
    {
        $user = $security->getUser();

        if (!$user){
            $this->addFlash('warning','Vous devez être connecté ou créer un compte.');
            return $this->redirectToRoute('app_login');
        }
        //on recupere le covoiturage selon son ID
        $covoiturage = $entityManager->getRepository(Covoiturage::class)->find($covoiturages->getId());
        $prix = $covoiturage->getPrix();
        $credit = $user->getCredits();
        $majPrix = $credit - $prix;
        $placeDispo = $covoiturage->getPlaceDispo();
        $majPlace = $placeDispo - 1;

        if (!$user){
            $this->addFlash('warning','Vous devez vous connecter avant de réserver un covoiturage');
            return $this->redirectToRoute('app_login');
        }
        if ($majPrix < 0) {
            $this->addFlash('warning', 'Crédits insuffisants pour réserver ce covoiturage.');
            return $this->redirectToRoute('app_profil');
        }

        if ($placeDispo = 0) {
            $this->addFlash('warning', 'Il n\'y a plus de place de disponible.');
            return $this->redirectToRoute('app_profil');
        }

        $user->setCredits($majPrix);
        $user->setPassager(true);
        $covoiturage->addUtilisateur($user);
        $covoiturage->setPlaceDispo($majPlace);

        $entityManager->persist($user);
        $entityManager->persist($covoiturage);

        $entityManager->flush();

        $this->addFlash('success', 'Vous êtes enregistré pour ce covoiturage.');
        return $this->redirectToRoute('app_profil'); // Redirection après succès
        
    }

     //validation participation covoiturage
        #[Route('/covoiturage/{id}/noParticipate', name: 'app_covoiturage_noparticipate', requirements: ['id'=>'\d+'])]

        public function noParticipate(Security $security,EntityManagerInterface $entityManager,Covoiturage $covoiturages) : Response
        
        {
            //on recupere le covoiturage selon son ID
            $covoiturage = $entityManager->getRepository(Covoiturage::class)->find($covoiturages->getId());
            $user = $security->getUser();
            $prix = $covoiturage->getPrix();
            $credit = $user->getCredits();
            $majPrix = $credit + $prix;
            $placeDispo = $covoiturage->getPlaceDispo();
            $majPlace = $placeDispo + 1;
    
            if (!$user){
                $this->addFlash('warning','Vous devez vous connecter avant d\'annuler un covoiturage');
                return $this->redirectToRoute('app_login');
            }
            
            $user->setCredits($majPrix);
            $user->setPassager(false);
            $covoiturage->removeUtilisateur($user);
            $covoiturage->setPlaceDispo($majPlace);
    
            $entityManager->persist($user);
            $entityManager->persist($covoiturage);
    
            $entityManager->flush();
    
            $this->addFlash('success', 'Votre particiption à ce covoiturage est annulée.');
            return $this->redirectToRoute('app_profil'); // Redirection après succès
            
        }

        #[Route('/covoiturage/{id}/go', name:'app_covoiturage_go', requirements:['id'=>'\d+'])]
    
        public function carGO(int $id,Covoiturage $covoiturage,EntityManagerInterface $entityManager,Security $security):Response

        {
            $user = $security->getUser();

            if (!$user){
                $this->addFlash('warning','Vous devez être connecté a votre compte.');
                $this->redirectToRoute('app_login');
            }

            //on recupere le covoiturage selon son ID

        $entityManager->getRepository(Covoiturage::class)->find($id);
        
        if (!$covoiturage) {
            throw $this->createNotFoundException("Le covoiturage avec l'ID {$id} n'existe pas.");
        }

        $covoiturage->setGo( 1 );

        $entityManager->persist($covoiturage);
        $entityManager->flush();

        $this->addFlash('success','Votre voyage voyage est en cours,bonne route.');
        return $this->redirectToRoute('app_profil');
    }

    #[Route('/covoiturage/{id}/stop', name:'app_covoiturage_stop', requirements:['id'=>'\d+'])]
    
    public function carStop(int $id,Covoiturage $covoiturage,EntityManagerInterface $entityManager,Security $security):Response

    {
        $user = $security->getUser();

        if (!$user){
            $this->addFlash('warning','Vous devez être connecté a votre compte.');
            $this->redirectToRoute('app_login');
        }

    //on recupere le covoiturage selon son ID

    $entityManager->getRepository(Covoiturage::class)->find($id);
    
    if (!$covoiturage) {
        throw $this->createNotFoundException("Le covoiturage avec l'ID {$id} n'existe pas.");
    }
    
    $go = $covoiturage->isGo();

    if ($go === false){

        $this->addFlash('warning','Ce covoiturage n\'a pas démarré');
        $this->redirectToRoute('app_login');

    } else {
    $covoiturage->setArrived( 1 );
    
    $entityManager->persist($covoiturage);
    $entityManager->flush();
    }

    $this->addFlash('success','Votre voyage est terminé.');
    return $this->redirectToRoute('app_profil');
}
        }
