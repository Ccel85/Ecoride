<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Voiture;
use App\Entity\Utilisateur;
use App\Form\CovoiturageFormType;
use App\Document\CovoiturageMongo;
use App\Repository\AvisRepository;
use App\Repository\VoitureRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CovoiturageRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CovoiturageController extends AbstractController
{
    //lister les covoiturages
    #[Route('/covoiturage', name: 'app_covoiturage')]
    
    public function index(
    VoitureRepository $voituresRepository,
    DocumentManager $documentManager): Response
    {

        $voitures = $voituresRepository->findAll();
        $covoiturages = $documentManager->getRepository(CovoiturageMongo::class)->findAll();

        return $this->render('covoiturage/index.html.twig', [
            'covoiturages'=>$covoiturages,
            'voitures'=>$voitures,
        ]);
    }

    // voir un covoiturage selon son Id
    #[Route('/covoiturage/{id}', name: 'app_covoiturage_id', requirements: ['id' => '.+']) ]
    
    public function détail(
    string $id,
    DocumentManager $documentManager,
    Security $security,
    EntityManagerInterface $em): Response
    
    {
        //on recupere le covoiturage selon son ID
        $covoiturage = $documentManager->getRepository(CovoiturageMongo::class)->find($id);
        if (!$covoiturage) {
            throw $this->createNotFoundException("Le covoiturage avec l'ID {$id} n'existe pas.");
        }

        // Récupération de l'utilisateur connecté
        $user = $security->getUser();

        $userCovoiturage = $user->getCovoiturage($covoiturage);

        // Vérifier si l'utilisateur est passager du covoiturage proposition a verifier:
        /* $userCovoiturage = in_array($user->getId(), $covoiturage->getPassagersIds()); */
        
        // Récupération des informations liées
        $conducteurId = $covoiturage->getConducteurId(); // L’ID du conducteur est stocké en MongoDB
        $conducteur = $em->getRepository(Utilisateur::class)->find($conducteurId); // Trouver l'utilisateur en base SQL
        if (!$conducteur) {
            throw $this->createNotFoundException("Le conducteur n'existe pas.");
        }
                
        $commentsUser = $em->getRepository(Avis::class)->findBy(['conducteur' => $conducteur]);
        $voitures = $em->getRepository(Voiture::class)->findBy(['utilisateur' => $conducteur]);
        $observations = $conducteur->getObservation();
        $rating = $em->getRepository(Avis::class)->rateUser($conducteur);
        $rateUser = $rating !== null ? round($rating, 1) : 0;
        
         // Scinder le texte par les virgules
        $observationExplode = explode(',' ,$observations);

        return $this->render('covoiturage/covoiturageDetail.html.twig', [
            'covoiturage'=>$covoiturage,
            'conducteur'=>$conducteur,
            'commentaires'=>$commentsUser,
            'voitures'=>$voitures,
            'observations'=>$observationExplode,
            'rateUser'=>$rateUser,
            'userCovoiturage'=>$userCovoiturage

        ]);
    }
    
    //Création covoiturage
    #[Route('/covoiturage/new', name: 'app_covoiturage_new')]
public function NewCovoiturage(
    Request $request,
    DocumentManager $documentManager,
    EntityManagerInterface $entityManager,
    Security $security,
    VoitureRepository $voitureRepository
): Response {

    $utilisateur = $security->getUser();

    if (!$utilisateur) {
        $this->addFlash('warning', 'Veuillez vous connecter ou créer un compte.');
        return $this->redirectToRoute('app_login');
    }

    // Récupérer les voitures associées à l'utilisateur
    $voitures = $voitureRepository->findBy(['utilisateur' => $utilisateur]);
  
    if (!$voitures) {
        $this->addFlash('warning', 'Veuillez tout d\'abord enregistrer votre véhicule.');
        return $this->redirectToRoute('app_voiture_new');
    }

    // Récupérer le crédit
    $credits = $utilisateur->getCredits();

    if ($credits < 2) {
        $this->addFlash('warning', 'Vous n\'avez pas assez de crédits pour créer un covoiturage');
        return $this->redirectToRoute('app_profil');
    }

    $majCredit = $credits - 2;

    $covoiturage = new CovoiturageMongo();

    $form = $this->createForm(CovoiturageFormType::class, $covoiturage, [
        'utilisateur' => $utilisateur, // Passer l'utilisateur connecté
        'voitures' => $voitures // Passer les voitures au formulaire
    ]);
    
    $form->handleRequest($request);
    
    if ($form->isSubmitted() && $form->isValid()) {
        
        $utilisateur = $security->getUser();

        //  Récupérer l'ID de la voiture sous forme d'entier
       /*  $voiture = $form->get('voitureId')->getData();
        $voitureId = $voiture ? (string) $voiture->getId() : null; // Convertit en string
        dump($voitureId); */

        $voiture = $form->get('voitureId')->getData();
    
        dump($voiture); // Vérifie si c'est un objet Voiture

        //  Extraire l'ID sous forme de string
        $voitureId = $voiture ? (string) $voiture->getId() : null;

    dump($voitureId); // Vérifie que c'est bien une string
    
        // Création du covoiturage
       
        $covoiturage->setVoitureId((string) $voitureId); // Force la conversion en string
        $covoiturage->setConducteurId((string) $utilisateur->getId());
    
        // Sauvegarde en MongoDB
        $documentManager->persist($covoiturage);
        $documentManager->flush();

        // Mettre à jour l'entité Utilisateur
        $utilisateur->setCredits($majCredit);
        $utilisateur->setConducteur(true);

        // Sauvegarde en SQL
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
    #[Route('/covoiturage/{id}/remove', name: 'app_covoiturage_remove' , requirements: ['id' => '.+']) ]

    public function RemoveCovoiturage( Security $security,
        DocumentManager $documentManager ,
        EntityManagerInterface $entityManager,
        SessionInterface $session, // Injecter la session
        CovoiturageMongo $covoiturage,
        $id): Response
    {

        $user = $security->getUser();

        if (!$user) {
            $this->addFlash('error', 'Utilisateur non connecté.');
            return $this->redirectToRoute('app_login');
        }

        $creditUser = $user->getCredits();
        
        // Récupération du covoiturage depuis MongoDB
        $covoiturage = $documentManager->getRepository(CovoiturageMongo::class)->find($id);

        if (!$covoiturage) {
            $this->addFlash('error', 'Covoiturage introuvable.');
            return $this->redirectToRoute('app_profil');
    }
        // Vérifier si l'utilisateur est autorisé à supprimer ce covoiturage
        if ($covoiturage->getConducteurId() !== $user->getId()) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à supprimer ce covoiturage.');
            return $this->redirectToRoute('app_profil');
        }
        // Récupérer les emails des utilisateurs participants
        $emails = [];
        foreach ($covoiturage->getUtilisateurs() as $utilisateur) {
            if ($utilisateur->getEmail()) {
                $emails[] = $utilisateur->getEmail();
            }
            $prix = $covoiturage->getPrix();
            $credit = $utilisateur->getCredits();
            $majCreditUtilisateur = $prix + $credit;
            $utilisateur ->setCredits($majCreditUtilisateur);
            $entityManager->persist($utilisateur);
        }

        // Stocker les emails en session
        $session->set('emails_utilisateurs', $emails);

        /* // Stocker l'ID avant de supprimer l'entité
        $covoiturageId = $covoiturage->getId(); */

        // Mise a jour du crédit
        $majCredits =$creditUser + 2;
        $user->setCredits($majCredits);
        $entityManager->persist($user);

        //suppression du covoiturage
       /*  $user->removeCovoiturage($covoiturage); */
        $documentManager->remove($covoiturage);
        $documentManager->flush();

        // Sauvegardez les modifications utilisateur et conducteur
        $entityManager->flush();

        $this->addFlash('success', 'Covoiturage supprimé avec succès!');

        return $this->redirectToRoute('app_send_email_remove', ['id' => $id]);

    }

    //Mise à jour des covoiturages proprietaire
    #[Route('/covoiturage/{id}/update', name: 'app_covoiturage_update',requirements: ['id' => '.+'])]

    public function UpdateCovoiturage(Security $security,
    Request $request,
    DocumentManager $documentManager ,
    EntityManagerInterface $entityManager,
    CovoiturageMongo $covoiturage,
    VoitureRepository $voiture): Response
    
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

            $documentManager->persist($form);
            $documentManager->flush();

            $this->addFlash('success', 'Covoiturage mis à jour avec succès.');
            return $this->redirectToRoute('app_profil'); // Redirection après succès
        }
            return $this->render('covoiturage/new.html.twig', [
                'form' => $form->createView(),
                'covoiturage' => $covoiturage,
                'voitures' => $voitures
            ]);
    }

    //Recherche covoiturage
    #[Route('/covoiturageRecherche', name: 'app_covoiturage_recherche', methods: ['GET'])]

    public function covoiturageRecherche(
    AvisRepository $avisRepository,
    CovoiturageRepository $repository,
    DocumentManager $documentManager,
    EntityManagerInterface $entityManager,
    Request $request): Response
    
    {
        

        // Récupérer les données du formulaire
        $dateDepart = $request->query->get('date');
        $lieuDepart = $request->query->get('depart');
        $lieuArrivee = $request->query->get('arrivee');
        $prix = $request->query->get('prix');
        $dureeMax = $request->query->get('dureeMax');
        $rate = $request->query->get('rate');

        if ($dureeMax) {
            // Convertir les heures en minutes pour la comparaison
            $intervalMax = new \DateInterval('PT' . ((int) $dureeMax) . 'H');
        }

        $submit = $request->query->has('date') ||
        $request->query->has('depart') ||
        $request->query->has('arrivee') ||
        $request->query->has('prix')||
        $request->query->has('dureeMax')||
        $request->query->has('rate');


        /* // Construction la requête pour filtrer les covoiturages
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

            $covoiturages = $queryBuilder->orderBy('c.dateDepart', 'ASC')
            ->getQuery()
        ->getResult(); */

        $covoiturages = $repository->findCovoiturage($dateDepart,$lieuDepart,$lieuArrivee,$prix);

        // Valeur par défaut
        $dureeVoyage = null;
        $rateUser = null;
        $dateFuture = false;

        foreach ($covoiturages as $key => $covoiturage) {
            
            // Vérifier si les covoiturages sont futurs
            $covoiturage->setDateFuture($covoiturage->getDateDepart() > new \DateTime());
            
            //calculer la durée du voyage
            $dureeVoyage =  $covoiturage->getHeureDepart()->diff($covoiturage->getHeureArrivee());

            //rechercher la note utilisateur
            $conducteur = $covoiturage->getConducteurId(); // Récupérer l'unique conducteur
            $rateUser =round($avisRepository->rateUser($conducteur),1); // Appeler la méthode sur l'objet conducteur
             // Ajouter la note directement à l'objet covoiturage
            $covoiturage->rate = $rateUser;
            
            if (isset($intervalMax) && ($dureeVoyage->h > $intervalMax->h)) {
                unset($covoiturages [$key]); // Supprimer ce covoiturage
            } else {
                $covoiturage->duree = $dureeVoyage->format('%h h %i min');
            }

            if (isset($rate) && ($rateUser < $rate)) {
                unset($covoiturages [$key]); // Supprimer ce covoiturage
            } else {
                $covoiturage->rate = $rateUser;
            }
        }
            // Si aucun covoiturage trouvé, chercher une date proche
            if (empty($covoiturages)) {

            $covoiturages = $repository->findCovoiturageByDateNear($dateDepart, $lieuDepart, $lieuArrivee);
            
            foreach ($covoiturages as $key => $covoiturage) {

                //on filtre les covoiturages futures
                $covoiturage->setDateFuture($covoiturage->getDateDepart() > new \DateTime());

                //calculer la durée du voyage
                $dureeVoyage =  $covoiturage->getHeureDepart()->diff($covoiturage->getHeureArrivee());

                //rechercher la note utilisateur
                $conducteur = $covoiturage->getConducteurId(); // Récupérer l'unique conducteur
                $rateUser =round($avisRepository->rateUser($conducteur),1);
                // Ajouter la note directement à l'objet covoiturage
                $covoiturage->rate = $rateUser;

                //filtre des covoiturages en fonction de la duree du voyage
                if (isset($intervalMax) && ($dureeVoyage->h > $intervalMax->h)) {
                    unset($covoiturages [$key]); // Supprimer ce covoiturage
                } else {
                    $covoiturage->duree = $dureeVoyage->format('%h h %i min');
                }

                //filtre des covoiturages en fonction de la duree du voyage
                if (isset($rate) && ($rateUser < $rate)) {
                    unset($covoiturages [$key]); // Supprimer ce covoiturage
                } else {
                    $covoiturage->rate = $rateUser;
                }
            }
        }
            return $this->render('covoiturage/index.html.twig', [
                'covoiturages'=>$covoiturages,
                'dateFuture'=>$dateFuture,
                'dateDepart'=>$dateDepart,
                'lieuDepart'=>$lieuDepart,
                'lieuArrivee'=>$lieuArrivee,
                'dureeMax'=>$dureeMax,
                'dureeVoyage'=>$dureeVoyage,
                'rateUser'=>$rateUser,
                'conducteur'=>$conducteur,
                'submit' => $submit,
                
            ]);
    }

    //validation participation covoiturage
    #[Route('/covoiturage/{id}/participate', name: 'app_covoiturage_participate', requirements: ['id' => '.+'])]

    public function participate(Security $security,
    DocumentManager $documentManager,
    EntityManagerInterface $entityManager,
    CovoiturageMongo $covoiturages) : Response
    
    {
        $user = $security->getUser();

        if (!$user){
            $this->addFlash('warning','Vous devez être connecté ou créer un compte.');
            return $this->redirectToRoute('app_login');
        }
        //on recupere le covoiturage selon son ID
        $covoiturage = $documentManager->getRepository(CovoiturageMongo::class)->find($covoiturages->getId());
        $prix = $covoiturage->getPrix();
        $credit = $user->getCredits();
        $majPrix = $credit - $prix;
        $placeDispo = $covoiturage->getPlaceDispo();
        
        if ($placeDispo > 0) {
        $majPlace = $placeDispo - 1;
        } else {
            $this->addFlash('warning','Réservation impossible,il n\'y a plus de place,veuillez choisir un autre voyage.');
            return $this->redirectToRoute('app_covoiturage_recherche');
        }

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
        $entityManager->persist($user);
        $entityManager->flush();

        $covoiturage->addUtilisateur($user);
        $covoiturage->setPlaceDispo($majPlace);

        $documentManager->persist($covoiturage);
        $documentManager->flush();

        $this->addFlash('success', 'Vous êtes enregistré pour ce covoiturage.');
        return $this->redirectToRoute('app_profil'); // Redirection après succès
        
    }

     //validation participation covoiturage
    #[Route('/covoiturage/{id}/noParticipate', name: 'app_covoiturage_noparticipate', requirements: ['id' => '.+'])]

    public function noParticipate(Security $security,
    DocumentManager $documentManager,
    EntityManagerInterface $entityManager,
    CovoiturageMongo $covoiturages) : Response
        
    {
        //on recupere le covoiturage selon son ID
        $covoiturage = $documentManager->getRepository(CovoiturageMongo::class)->find($covoiturages->getId());
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

        $entityManager->persist($user);
        $entityManager->flush();

        $covoiturage->removeUtilisateur($user);
        $covoiturage->setPlaceDispo($majPlace);

        $documentManager->persist($covoiturage);
        $documentManager->flush();


        $this->addFlash('success', 'Votre particiption à ce covoiturage est annulée.');
        return $this->redirectToRoute('app_profil'); // Redirection après succès
        
    }

    #[Route('/covoiturage/{id}/go', name:'app_covoiturage_go', requirements: ['id' => '.+'])]
    
    public function carGO(int $id,
    CovoiturageMongo $covoiturage,
    DocumentManager $documentManager,
    EntityManagerInterface $entityManager,
    Security $security):Response

    {
            $user = $security->getUser();

            if (!$user){
                $this->addFlash('warning','Vous devez être connecté a votre compte.');
                $this->redirectToRoute('app_login');
            }

            //on recupere le covoiturage selon son ID

        $documentManager->getRepository(CovoiturageMongo::class)->find($id);
        
        if (!$covoiturage) {
            throw $this->createNotFoundException("Le covoiturage avec l'ID {$id} n'existe pas.");
        }

        $covoiturage->setGo( 1 );

        $documentManager->persist($covoiturage);
        $documentManager->flush();

        $this->addFlash('success','Votre voyage est en cours,bonne route.');
        return $this->redirectToRoute('app_profil');
    }

    #[Route('/covoiturage/{id}/stop', name:'app_covoiturage_stop', requirements: ['id' => '.+'])]
    
    public function carStop(int $id,
    CovoiturageMongo $covoiturage,
    DocumentManager $documentManager,
    Security $security):Response

    {
        $user = $security->getUser();

        if (!$user){
            $this->addFlash('warning','Vous devez être connecté a votre compte.');
            $this->redirectToRoute('app_login');
        }

        //on recupere le covoiturage selon son ID
        $documentManager->getRepository(CovoiturageMongo::class)->find($id);
        
        if (!$covoiturage) {
            throw $this->createNotFoundException("Le covoiturage avec l'ID {$id} n'existe pas.");
        }
        
        $go = $covoiturage->isGo();

        if ($go === false){

            $this->addFlash('warning','Ce covoiturage n\'a pas démarré');
            $this->redirectToRoute('app_login');

        } else {
        $covoiturage->setArrived( 1 );
        
        $documentManager->persist($covoiturage);
        $documentManager->flush();

        }

        $this->addFlash('success','Votre voyage est terminé.');
        return $this->redirectToRoute('app_send_email', ['id' => $covoiturage->getId()]);
    }

//validation du covoiturage par les voyageurs
#[Route('/covoiturage/{id}/validate', name:'app_covoiturage_validate', requirements: ['id' => '.+'])]
    
    public function validateCovoiturage(int $id,
    DocumentManager $documentManager,
    EntityManagerInterface $entityManager,
    Security $security):Response
    {
        $user = $security->getUser();

        if (!$user){
            $this->addFlash('warning','Vous devez être connecté a votre compte.');
            $this->redirectToRoute('app_login');
        }

        //on recupere le covoiturage selon son ID
        $covoiturage = $documentManager->getRepository(CovoiturageMongo::class)->find($id);
        
        if (!$covoiturage) {
            throw $this->createNotFoundException("Le covoiturage avec l'ID {$id} n'existe pas.");
        }
        $isValidate = $user->getValidateCovoiturages()->contains($covoiturage);
       // Vérifiez si l'utilisateur a déjà validé ce covoiturage
        if ($isValidate) {

        $this->addFlash('warning', 'Vous avez déjà validé ce voyage.');
        return $this->redirectToRoute('app_profil');

        } else {
        // Ajouter l'utilisateur aux validateUsers
            $covoiturage->addValidateUser($user);

            $conducteur = $covoiturage->getConducteurId();
            $prix = $covoiturage->getPrix();
            $newCredit = $conducteur->setCredits($conducteur->getCredits() + $prix);
            
            $documentManager->persist($covoiturage);
            $documentManager->flush();

            $entityManager->persist($newCredit);
            $entityManager->flush();

           /*  return new JsonResponse(['success' => true, 'message' => 'Covoiturage validé']); */
            $this->addFlash('success', 'Covoiturage validé avec succès !');
        
            return $this->redirectToRoute('app_profil');
        }
    }
}
