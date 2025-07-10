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
use App\Repository\UtilisateurRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\CovoiturageMongoRepository;
use Knp\Component\Pager\Paginator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;

class CovoiturageController extends AbstractController
{
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

//Suppression du covoiturage par le conducteur
#[Route('/covoiturage/{id}/remove', name: 'app_covoiturage_remove' , requirements: ['id' => '.+']) ]
        public function RemoveCovoiturage( Security $security,
            DocumentManager $documentManager ,
            EntityManagerInterface $entityManager,
            UrlGeneratorInterface $urlGenerator,
            SessionInterface $session, // Injecter la session
            CovoiturageMongo $covoiturage,
            $id): Response
        {

            $user = $security->getUser();
            $userId = (string) $user->getId(); // Convertit l'ID en string
            if (!$user) {
                $this->addFlash('error', 'Utilisateur non connecté.');
                return $this->redirectToRoute('app_login');
            }

            $creditUser = $user->getCredits();
            
            // Récupération du covoiturage depuis MongoDB
            $covoiturage = $documentManager->getRepository(CovoiturageMongo::class)->find($id);
            /* $urlCovoiturageRecherche = $urlGenerator->generate('app_covoiturage_recherche',[],
                                    UrlGeneratorInterface::ABSOLUTE_URL); */
            if (!$covoiturage) {
                $this->addFlash('error', 'Covoiturage introuvable.');
                return $this->redirectToRoute('app_profil');
        }
            // Vérifier si l'utilisateur est autorisé à supprimer ce covoiturage
            if ($covoiturage->getConducteurId() !== $userId) {
                $this->addFlash('error', 'Vous n\'êtes pas autorisé à supprimer ce covoiturage.');
                return $this->redirectToRoute('app_profil');
            }
            // Récupérer les emails des utilisateurs participants
            $emails = [];

            foreach ($covoiturage->getPassagersIds() as $passagerId) {
                $passager = $entityManager->getRepository(Utilisateur::class)->find($passagerId);
                if ($passager && $passager->getEmail()) {
                    $emails[] = $passager->getEmail();
                }
            }
            $prix = $covoiturage->getPrix();
            $credit = $passager->getCredits();
            $majCreditPassager = $prix + $credit;
            //maj credits passager
            $passager ->setCredits($majCreditPassager);
            $entityManager->persist($passager);

            // Stocker les emails en session
            $session->set('emails_utilisateurs', $emails);
            $session->set('covoiturage', $covoiturage);

            /* // Stocker l'ID avant de supprimer l'entité
            $covoiturageId = $covoiturage->getId(); */

            // Mise a jour du crédit conducteur
            $majCredits =$creditUser + 2;
            $user->setCredits($majCredits);
            $entityManager->persist($user);

            // Sauvegardez les modifications utilisateur et conducteur
            $entityManager->flush();

            //suppression du covoiturage
            $documentManager->remove($covoiturage);
            $documentManager->flush();

            if (count($emails) > 0) {
                $this->addFlash('success', 'Covoiturage supprimé avec succès!');

                return $this->redirectToRoute('app_send_email_remove', ['id' => $id,/* 'urlCovoiturageRecherche' => $urlCovoiturageRecherche */]);

            } else {
                $this->addFlash('success', 'Aucun passager n\'était inscrit.
                Le covoiturage a été supprimé avec succès !');

                return $this->redirectToRoute('app_profil');
            }

         /*    $this->addFlash('success', 'Covoiturage supprimé avec succès!');

            return $this->redirectToRoute('app_send_email_remove', ['id' => $id]); */
    }

//Mise à jour des covoiturages par le conducteur
#[Route('/covoiturage/{id}/update', name: 'app_covoiturage_update',requirements: ['id' => '.+'])]

    public function UpdateCovoiturage(
        Security $security,
        Request $request,
        DocumentManager $documentManager ,
        EntityManagerInterface $em,
        CovoiturageMongo $covoiturage): Response
    
    {
        $utilisateur = $security->getUser();
        $userId = (string) $utilisateur->getId(); // Convertit l'ID en string
        
        if (!$utilisateur) {
            $this->addFlash('error', 'Utilisateur non connecté.');
            return $this->redirectToRoute('app_login');
        }

        $voitures = $em->getRepository(Voiture::class)->findBy(['utilisateur' => $utilisateur]);

        // Vérifier si l'utilisateur est autorisé à modifier ce covoiturage
        if ($covoiturage->getConducteurId() !== $userId){ //!$utilisateur->getCovoiturage()->contains($covoiturage)) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à modifier ce covoiturage.');
            return $this->redirectToRoute('app_profil');
        }

        $form = $this->createForm(CovoiturageFormType::class, $covoiturage,[
            'voitures' => $voitures, // Liste des voitures récupérées
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $documentManager->persist($covoiturage);
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
        Security $security,
        AvisRepository $avisRepository,
        DocumentManager $dm,
        EntityManagerInterface $entityManager,
        PaginatorInterface $paginator,
        Request $request): Response
    {
        // Récupérer les données du formulaire
        $date = $request->query->get('date');
        dump($date);
        $lieuDepart = $request->query->get('depart');
        $lieuArrivee = $request->query->get('arrivee');
        $prix = $request->query->get('prix');
        $dureeMax = $request->query->get('dureeMax');
        $rate = $request->query->get('rate');

         // Valeur par défaut
        $dureeVoyage = null;
        $rateUser = null;
        $dateFuture = false;
        $conducteurs = [];
        $voitures = null;
        $avis = null;
        $covoiturageRepository = $dm->getRepository(CovoiturageMongo::class);
        $covoiturages = null;
        $avisExistants[]= false;

        // Convertir les heures en minutes pour la comparaison
        $intervalMax = $dureeMax ? new \DateInterval('PT' . ((int) $dureeMax) . 'H'):null;

        $buttonSearch = $request->query->has('valid');
        
        $submit = $buttonSearch ||
            trim((string)$request->query->get('date')) !== '' ||
            trim((string)$request->query->get('depart')) !== '' ||
            trim((string)$request->query->get('arrivee')) !== '' ||
            trim((string)$request->query->get('prix')) !== '' ||
            trim((string)$request->query->get('dureeMax')) !== '' ||
            trim((string)$request->query->get('rate')) !== '';

            try {
                    if (!$date) {
                    // Si aucune recherche : par défaut, on prend aujourd'hui à minuit en datetime
                        $dateDepart = (new \DateTime('today'));

                    } elseif (!empty($date)) {
                    // Si l'utilisateur a mis une date dans le filtre on la modifie en datetime
                        $dateDepart = (new \DateTime($date));
                        
                    }
                } catch (\Exception $e) {
                    // Si Mauvais format de date 
                    $this->addFlash('warning', 'Format de date invalide. Résultats affichés à partir d’aujourd’hui.');
                    $dateDepart = (new \DateTime('today'));
                }
                $notCovoiturages = false;
                if ($submit){
                    //Recuperer les covoiturages en fonction des criteres saisis
                    $covoiturages = $covoiturageRepository->findCovoiturage($dateDepart, $lieuDepart, $lieuArrivee, $prix);
                    dump($covoiturages);
                        
                    // Aucun covoiturage exact, on cherche des covoiturages proches
                    if (empty($covoiturages)) {
                        $notCovoiturages = true;
                        $covoiturages = $covoiturageRepository->findCovoiturageByDateNear($dateDepart, $lieuDepart, $lieuArrivee, $prix);
                    
                    }
                } else {
                    // Aucun critère saisi => chercher autour de la date du jour
                    $covoiturages = $covoiturageRepository->findCovoiturageByDateNear($dateDepart, $lieuDepart, $lieuArrivee, $prix);
                    $notCovoiturages = true;
                    
                }

        foreach ($covoiturages as $key => $covoiturage) {

            //On filtre les covoiturages futures
            $covoiturage->setDateFuture($covoiturage->getDateDepart() > new \DateTime());

            //Calculer la durée du voyage
            $dureeVoyage =  $covoiturage->getHeureDepart()->diff($covoiturage->getHeureArrivee());

            //Rechercher le véhicule du covoiturage
            $voitureId = $covoiturage->getVoitureId();
            $voiture = $entityManager->getRepository(Voiture::class)->find($voitureId);
            $voitures[$key] = $voiture;

            //Rechercher l'objet conducteur en base
            $conducteurId = $covoiturage->getConducteurId(); // Récupérer l'unique conducteur
            $conducteur = $entityManager->getRepository(Utilisateur::class)->find($conducteurId);
            $conducteurs[$key] = $conducteur;

            if (!$conducteur) {
                throw new \Exception("Conducteur non trouvé avec l'ID: " . $conducteurId);
            }

            //Recherche les avis du covoiturage
            $avis = $avisRepository->findOneBy(['conducteur' => $conducteur]);
            $avisExistants[$key] = $avis ? true : false;
            $rateUser =round($avisRepository->rateUser($conducteur),1);

            //Filtre des covoiturages en fonction de la duree du voyage
            if (isset($intervalMax) && ($dureeVoyage->h > $intervalMax->h)) {
                unset($covoiturages [$key]); // Supprimer ce covoiturage
            } else {
                $covoiturage->duree = $dureeVoyage->format('%h h %i min');
            }

            //Filtre des covoiturages en fonction de la note utilisateur
            if (isset($rate) && ($rateUser < $rate)) {
                unset($covoiturages [$key]); // Supprimer ce covoiturage
            } else {
                // Ajouter la note directement à l'objet covoiturage
                $covoiturage->rate = $rateUser;
            }

             //Filtre des covoiturages si deja inscrit au voyage
            $user = $security->getUser();
            if ($user){
                $userId = $user->getId();
                if (in_array($userId, $covoiturage->getPassagersIds())) {
            unset($covoiturages[$key]); // Supprime ce covoiturage de la liste
            }
            }

            $pagination = $paginator->paginate(
            $covoiturages, 
            $request->query->getInt('page', 1), // numéro de page
            9 // nombre d'éléments par page
            );
        }
        
            return $this->render('covoiturage/index.html.twig', [
            'covoiturages'=>$covoiturages,
            'notCovoiturages'=>$notCovoiturages,
            //'dateFuture'=>$dateFuture,
            'dateDepart'=>$dateDepart,
            'lieuDepart'=>$lieuDepart,
            'lieuArrivee'=>$lieuArrivee,
            'dureeMax'=>$dureeMax,
            'dureeVoyage'=>$dureeVoyage,
            'rateUser'=>$rateUser,
            'avisExistants' => $avisExistants,
            'avis'=>$avis,
            'conducteurs'=>$conducteurs,
            'submit' => $submit,
            'voitures'=>$voitures,
            'pagination' => $pagination,
            
        ]);
    
    }

//demande participation covoiturage par le passager
#[Route('/covoiturage/{id}/participate', name: 'app_covoiturage_participate', requirements: ['id' => '.+'])]

    public function participate(
        string $id,
        Security $security,
        DocumentManager $documentManager,
        EntityManagerInterface $entityManager
        ) : Response
    
    {
        $user = $security->getUser();
        
        if (!$user){
            $this->addFlash('warning','Vous devez être connecté ou créer un compte.');
            return $this->redirectToRoute('app_login');
        }
        
        //on recupere le covoiturage selon son ID
        $covoiturage = $documentManager->getRepository(CovoiturageMongo::class)->find ($id);
        $prix = $covoiturage->getPrix();
        $credit = $user->getCredits();
        $majcredit = $credit - $prix;
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
        if ($credit < $prix) {
            $this->addFlash('warning', 'Votre crédits est insuffisants pour réserver ce covoiturage.');
            return $this->redirectToRoute('app_profil');
        }
        if ($placeDispo = 0) {
            $this->addFlash('warning', 'Il n\'y a plus de place de disponible.');
            return $this->redirectToRoute('app_profil');
        }
        $user->setCredits($majcredit);
        $entityManager->persist($user);
        $entityManager->flush();

        $covoiturage->setPlaceDispo($majPlace);
        $documentManager->persist($covoiturage);
        $documentManager->flush();

        //a partir de la envoyer email pour validation au conducteur et 
        return $this->redirectToRoute('app_send_email_reservation');

        //2- si validation  alors :
       /*  $user->setPassager(true);
        $entityManager->persist($user);
        $entityManager->flush();

        $covoiturage->addPassagersIds($user->getId());
        $documentManager->persist($covoiturage);
        $documentManager->flush();
 */
       /*  $this->addFlash('success', 'Vous êtes enregistré pour ce covoiturage.');
        return $this->redirectToRoute('app_profil');  */
        //envoyer email de confirmation au passager

        //si refus du conducteur pour ce passager
        //email a envoyer au passager de refus 

    }

//participation valider par conducteur
#[Route('/covoiturage/participateValid/{id}/{passagerId}', name: 'app_covoiturage_participateValid', requirements: ['id' => '.+'])]

    public function participateValid(
        string $id,
        int $passagerId,
        Security $security,
        DocumentManager $documentManager,
        EntityManagerInterface $entityManager,
        UtilisateurRepository $utilisateurRepository
        ) : Response
    
    {
        //conducteur
        $user = $security->getUser();

        if (!$user){
            $this->addFlash('warning','Vous devez être connecté ou créer un compte.');
            return $this->redirectToRoute('app_login');
        }

        $covoiturage = $documentManager->getRepository(CovoiturageMongo::class)->find ($id);
        if (!$covoiturage) {
        throw $this->createNotFoundException('Covoiturage introuvable');
    }
        /* //si validation  alors : 
        $passager->setPassager(true);
        $entityManager->persist($passager);
        $entityManager->flush(); */

        $passager = $utilisateurRepository->find($passagerId);
        if (!$passager) {
        throw $this->createNotFoundException('Passager introuvable');
    }

        //ajout du passager au covoiturage
        $covoiturage->addPassagersIds($passager->getId());
        $documentManager->persist($covoiturage);
        $documentManager->flush();


        $this->addFlash('success', 'Le passager a été validé avec succès.');
        return $this->redirectToRoute('app_send_email_valid_reservation', [
    'id'          => $covoiturage->getId(),
    'passagerId'  => $passager->getId(),
    ]);
    }
//refus du conducteur pour ce passager
#[Route('/covoiturageParticipateInvalid/{id}/{passagerId}', name: 'app_covoiturage_ParticipateInvalid', requirements: ['id' => '.+'])]

    public function ParticipateInvalid(
        string $id,
        int $passagerId,
        Security $security,
        DocumentManager $documentManager,
        EntityManagerInterface $entityManager,
        UtilisateurRepository $utilisateurRepository
        ) : Response
    {
        $covoiturage = $documentManager->getRepository(CovoiturageMongo::class)->find ($id);
        if (!$covoiturage) {
        throw $this->createNotFoundException('Covoiturage introuvable');
        }

        $passager = $utilisateurRepository->find($passagerId);
            if (!$passager) {
            throw $this->createNotFoundException('Passager introuvable');
        }
        //email a envoyer au passager refusé 
        $placeDispo = $covoiturage->getPlaceDispo();
        $majPlace = $placeDispo + 1;
        $prix = $covoiturage->getPrix();
        $credit = $passager->getCredits();
        $majcredit = $credit + $prix;

        $passager->setCredits($majcredit);
        //$passager->setPassager(false);
        $entityManager->persist($passager);
        $entityManager->flush();

        $covoiturage->setPlaceDispo($majPlace);
        $documentManager->persist($covoiturage);
        $documentManager->flush();


        //email a envoyer au passager de refus 

        $this->addFlash('success', 'Un mail notifiant le refus a été envoyé au passagé.');
        return $this->redirectToRoute('app_profil');

    }

//si passager annule covoiturage
#[Route('/covoiturage/{id}/noParticipate', name: 'app_covoiturage_noparticipate', requirements: ['id' => '.+'])]

    public function noParticipate(
        string $id,
        Security $security,
        DocumentManager $documentManager,
        EntityManagerInterface $entityManager
        ) : Response
        
    {
        //on recupere le covoiturage selon son ID
        $covoiturage = $documentManager->getRepository(CovoiturageMongo::class)->find($id);
        $user = $security->getUser();
        $userId = (string) $user->getId(); // Convertit l'ID en string
        $prix = $covoiturage->getPrix();
        $credit = $user->getCredits();
        $majCredit = $credit + $prix;
        $placeDispo = $covoiturage->getPlaceDispo();
        $majPlace = $placeDispo + 1;

        if (!$user){
            $this->addFlash('warning','Vous devez vous connecter avant d\'annuler un covoiturage');
            return $this->redirectToRoute('app_login');
        }
        
        $user->setCredits($majCredit);
        $user->setPassager(false);

        $entityManager->persist($user);
        $entityManager->flush();

        $covoiturage->removePassagersIds($userId);
        $covoiturage->setPlaceDispo($majPlace);

        $documentManager->persist($covoiturage);
        $documentManager->flush();


        $this->addFlash('success', 'Votre participation à ce covoiturage est annulée.');
        return $this->redirectToRoute('app_profil'); // Redirection après succès
        
    }

//validé le depart du covoiturage par le conducteur
#[Route('/covoiturage/{id}/go', name:'app_covoiturage_go', requirements: ['id' => '.+'])]
    
    public function carGO(
        string $id,
        //CovoiturageMongo $covoiturage,
        DocumentManager $documentManager,
        Security $security
        ):Response

        {
            $user = $security->getUser();

            if (!$user){
                $this->addFlash('warning','Vous devez être connecté a votre compte.');
                $this->redirectToRoute('app_login');
            }
            //on recupere le covoiturage selon son ID
            $covoiturage=$documentManager->getRepository(CovoiturageMongo::class)->find($id);
            
            if (!$covoiturage) {
                throw $this->createNotFoundException("Le covoiturage avec l'ID {$id} n'existe pas.");
            }

            $covoiturage->setGo( 1 );

            $documentManager->persist($covoiturage);
            $documentManager->flush();

            $this->addFlash('success','Votre voyage est en cours,bonne route.');
            return $this->redirectToRoute('app_profil');
    }

//valide l'arrivée du covoiturage par le conducteur
#[Route('/covoiturage/{id}/stop', name:'app_covoiturage_stop', requirements: ['id' => '.+'])]
    
    public function carStop(
        string $id,
        //CovoiturageMongo $covoiturage,
        DocumentManager $documentManager,
        Security $security
        ):Response

    {
        $user = $security->getUser();

        if (!$user){
            $this->addFlash('warning','Vous devez être connecté a votre compte.');
            $this->redirectToRoute('app_login');
        }

        //on recupere le covoiturage selon son ID
        $covoiturage= $documentManager->getRepository(CovoiturageMongo::class)->find($id);
        
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

//valider le voyage terminé par les passagers
#[Route('/covoiturage/{id}/validate', name:'app_covoiturage_validate', requirements:['id'=>'.+'])]
    
    public function validateCovoiturage(
        string $id,
        DocumentManager $documentManager,
        EntityManagerInterface $entityManager,
        Security $security):Response
    {
            $user = $security->getUser();
            $userId = $user->getId();

            if (!$user){
                $this->addFlash('warning','Vous devez être connecté a votre compte.');
                $this->redirectToRoute('app_login');
            }

            //on recupere le covoiturage selon son ID
            $covoiturage = $documentManager->getRepository(CovoiturageMongo::class)->find($id);
            
            if (!$covoiturage) {
                throw $this->createNotFoundException("Le covoiturage avec l'ID {$id} n'existe pas.");
            }

            $isValidate = in_array($userId,$covoiturage->getValidateUsers());
                
            // Vérifiez si l'utilisateur a déjà validé ce covoiturage
            if ($isValidate) {

                $this->addFlash('warning', 'Vous avez déjà validé ce voyage.');
                return $this->redirectToRoute('app_profil');
            } else {
                // Ajouter l'utilisateur aux validateUsers
                $covoiturage->addValidateUser($userId);

                $conducteurId = $covoiturage->getConducteurId();
                $conducteur = $entityManager->getrepository(Utilisateur::class)->find($conducteurId);
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
            $userCovoiturage = null;
            if ($user) {
                $userId = (string) $user->getId();
                //récupérer si user est déja enregistré passager de ce covoiturage
                $userCovoiturage =$covoiturage->getPassagersIds($userId);
            }
            
            // Récupération des informations liées
            $conducteurId = $covoiturage->getConducteurId(); // L’ID du conducteur est stocké en MongoDB
            $conducteur = $em->getRepository(Utilisateur::class)->find($conducteurId); // Trouver l'utilisateur en base SQL
            if (!$conducteur) {
                throw $this->createNotFoundException("Le conducteur n'existe pas.");
            }
            $voitureId = $covoiturage->getVoitureId();
            $voiture = $em->getRepository(Voiture::class)->find($voitureId); 
            //récupération des données:
            //récupérer les commentaires sur le conducteur:
            $commentsUser = $em->getRepository(Avis::class)->findBy(['conducteur' => $conducteur]);
            //$voitures = $em->getRepository(Voiture::class)->findBy(['utilisateur' => $conducteur]);
            $observations = $conducteur->getObservation();
            $rating = $em->getRepository(Avis::class)->rateUser($conducteur);
            $rateUser = $rating !== null ? round($rating, 1) : 0;
            
            // Scinder le texte par les virgules
            $observationExplode = explode(',' ,$observations);

            return $this->render('covoiturage/covoiturageDetail.html.twig', [
                'covoiturage'=>$covoiturage,
                'conducteur'=>$conducteur,
                'commentaires'=>$commentsUser,
                'voiture'=>$voiture,
                'observations'=>$observationExplode,
                'rateUser'=>$rateUser,
                'userCovoiturage'=>$userCovoiturage,
                'conducteurId'=>$conducteurId,

            ]);
    }
}
