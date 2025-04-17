<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Voiture;
use App\Entity\Utilisateur;
use App\Form\ProfilFormType;
use App\Document\CovoiturageMongo;
use App\Repository\AvisRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UtilisateurRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UtilisateurController extends AbstractController
{
    #[Route('/utilisateur', name: 'app_utilisateur')]
    public function indexUtilisateur(Security $security,
    UtilisateurRepository $utilisateurRepository,
    EntityManagerInterface $em): Response
    {
        $utilisateurs = $utilisateurRepository->findByRole("ROLE_USER");
        $user = $security->getUser();

        $rates = [];
        foreach ($utilisateurs as $utilisateur) {
        $rating = $em->getRepository(Avis::class)->rateUser($utilisateur);
        $rates [$utilisateur->getId()] =round($rating ?? 0,1);
        }
        if (!$utilisateurs){
            throw $this->createNotFoundException('Utilisateurs non trouvé.');
        }

        return $this->render('utilisateur/index.html.twig', [
            'utilisateurs' => $utilisateurs,
            'rates' => $rates,
            'user' =>$user,
        ]);
    }

    //liste employe
    #[Route('/employe', name: 'app_employe')]
    public function indexEmploye(UtilisateurRepository $utilisateurRepository): Response
    {
        $employes = $utilisateurRepository->findByRole('ROLE_EMPLOYE');
        
        if (!$employes){
            throw $this->createNotFoundException('Utilisateurs non trouvé.');
        }

        return $this->render('utilisateur/employe.html.twig', [
            'employes' => $employes,
        ]);
    }

    #[Route('/utilisateur/{id}/archive', name: 'app_utilisateur_archive', requirements: ['id' => '\d+'])]
    public function archiveUtilisateur(int $id,
    EntityManagerInterface $em,
    Security $security): Response
    {
        //récuperation de l'utilisateur
        $repository = $em->getRepository(Utilisateur::class);
        $utilisateur = $repository->find($id);
        
        if (!$utilisateur) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }
        //archiver l'utilisateur
        $utilisateur->setActif(false);
        $em->flush();

         // récuperation du role de l'utlisateur connecté
        $user = $security->getUser();
        $roleUser = $user->getRoles();

        $this->addFlash('success', 'L\'archivage a été effectué pour le profil concerné .');

        //redirection en fonction du role
        if ($roleUser == ['ROLE_ADMIN']){

            //retour sur la liste des employés
            return $this->redirectToRoute('app_employe');

        } else {
            //retour sur liste utilisateurs
            return $this->redirectToRoute('app_utilisateur');

        }
    }

    //Rendre utilisateur actif
    #[Route('/utilisateur/{id}/active', name: 'app_utilisateur_active', requirements: ['id' => '\d+'])]
    
    public function activeUtilisateur(int $id,
    EntityManagerInterface $em,
    Security $security): Response
    {
        //récuperation de l'utilisateur
        $repository = $em->getRepository(Utilisateur::class);
        $utilisateur = $repository->find($id);

        if (!$utilisateur) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }
        
                $utilisateur->setActif(true);
                $em->flush();

        // récuperation du role de l'utlisateur connecté
        $user = $security->getUser();
        $roleUser = $user->getRoles();

        $this->addFlash('success', 'L\'activation a été effectué pour le profil concerné.');
        
        //redirection en fonction du role
        if ($roleUser == ['ROLE_ADMIN']){
        //retour sur la liste des employés  
            return $this->redirectToRoute('app_employe');

        } else {
             //retour sur liste utilisateurs
            return $this->redirectToRoute('app_utilisateur');
        }
    }

    //Affichage profil connecté
    #[Route('/profil', name: 'app_profil')]
    
    public function profilUtilisateur(Security $security,
    EntityManagerInterface $em,
    DocumentManager $documentManager,
    AvisRepository $avisRepository): Response
    {
        $user = $security->getUser(); // Récupérer l'utilisateur connecté
        
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        $userId = (string) $user->getId(); // Convertit l'ID en string

        //récupération des covoiturages de l'utilisateur connecté
            //récupération quand conducteur (string):
        $covoituragesConducteur = $documentManager->getRepository(CovoiturageMongo::class)
                        ->findBy(['conducteurId' => $userId]);
        dump($covoituragesConducteur);
            //récupération quand passager (int):
        $covoituragesPassager = $documentManager->getRepository(CovoiturageMongo::class)
                        ->findBy(['passagersIds' => $userId]);
        dump($covoituragesPassager);
        //association de tous les covoiturages:
        $covoiturages = array_merge($covoituragesConducteur, $covoituragesPassager);

       /*  foreach ($covoiturages as $covoiturage){
            $conducteurId = (int) $covoiturage->getConducteurId();
            $conducteur = $em->getRepository(Utilisateur::class)->findOneBy(['id' => $conducteurId]);
            
        }
        dump($conducteur); */
        // Récuperation des données:
        $commentairesUser = $em->getRepository(Avis::class)->findCommentairesByUserOrdered($user);
        $voitureUser = $em->getRepository(Voiture::class)->findBy(['utilisateur' => $user]);
        /* $covoiturages = $user->getCovoiturage(); */
        $validatedCovoiturages = $documentManager->getRepository(CovoiturageMongo::class)
        ->findBy(['validateUsers' => $user->getId()]);
        $observations = $user->getObservation();
        $rating = $em->getRepository(Avis::class)->rateUser($user);
        $rateUser =round($rating ?? 0,1);

        // Scinder le texte par les virgules
        $observationExplode = explode(',' ,$observations);
        
        //Verification si Chauffeur qu'un véhicule soit enregistré
        if ($user->isConducteur(true) && !$voitureUser){
            $this->addFlash('warning', 'Vous êtes un conducteur, vous devez ajouter un véhicule !');
            return $this->redirectToRoute('app_voiture_new');
        }

        $dateAujourdhui = false;
        $isValidateUser = false;
        $avisUserExiste = false;
        $avisUser = null;

        if ($covoituragesConducteur !== null) {
            foreach ($covoiturages as $covoiturage) {
                
                $conducteurId = (int) $covoiturage->getConducteurId();
                $conducteur = $em->getRepository(Utilisateur::class)->findOneBy(['id' => $conducteurId]);
                
                //selectionner les dates futures à la date du jour
                $now = new \DateTimeImmutable();
                $dateFuture = $covoiturage->getDateDepart() > $now;
                $covoiturage->setDateFuture($dateFuture) ;
                
                $dateAujourdhui = $covoiturage->getDateDepart()->format('d-m-Y') === $now->format('d-m-Y');
                if ($dateAujourdhui){
                    $dateAujourdhui = $covoiturage->setDateAujourdhui($dateAujourdhui);
                }

                /* // Affiche tous les covoiturages validés par l'utilisateur
                if ($isValidateUser = $validatedCovoiturages->contains($covoiturage)){
                    // Vérifie si le covoiturage en question est validé
                $isValidateUser = true;
                break;
                } */
                
            }
        }
        //AVIS LAISSE PAR L'UTILISATEUR
        $avisUser = $avisRepository->findOneBy([
            'passager' => $user,
        ]);
        $avisUserExiste = $avisUser !== null;

            return $this->render('utilisateur/profil.html.twig', [
                'utilisateur' => $user,
                'commentairesUSer'=> $commentairesUser,
                'voitureUser'=> $voitureUser,
                'covoiturages'=> $covoiturages,
                'observations'=>$observationExplode,
                'dateAujourdhui'=>$dateAujourdhui,
                'isValidateUser'=>$isValidateUser,
                'rate'=>$rateUser,
                'avisUserExiste'=>$avisUserExiste,
                'conducteur'=>$conducteur,
                //'conducteur'=>$covoituragesConducteur,
                ]);
        }
    

    //affichage profil selon Id
    #[Route('/profil/{id}', name: 'app_profil_id' ,requirements: ['id' => '\d+'])]
    public function profil(
        int $id,
        EntityManagerInterface $em,
        DocumentManager $documentManager): Response
        {
        //recuperer l'utilisateur selon son ID (int)
        $user = $em->getRepository(Utilisateur::class)->find($id);
        
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        //mettre l'id user au format string
        $userId =(string) $user->getId();
        
        // Récuperation des données:

        //récupération des covoiturages de l'utilisateur connecté
            //récupération quand conducteur (string):
        $covoituragesConducteur = $documentManager->getRepository(CovoiturageMongo::class)
            ->findBy(['conducteurId' => $userId]);
            //dump($covoituragesConducteur);
        //récupération quand passager (int):
        $covoituragesPassager = $documentManager->getRepository(CovoiturageMongo::class)
            ->findBy(['passagersIds' => $user->getId()]);
            //dump($covoituragesPassager);
        //association de tous les covoiturages de l'utilisateur:
        $covoiturages = array_merge($covoituragesConducteur, $covoituragesPassager);
        //dump($covoiturages);

        // Récupération des données utilisateur
        $observations = $user->getObservation();
        // Scinder le texte par les virgules
        $observationExplode = explode(',' ,$observations);

        $commentairesUser = $em->getRepository(Avis::class)->findCommentairesByUserOrdered($user);
        $rateUser =round($em->getRepository(Avis::class)->rateUser($user),1);
        $voitureUser = $em->getRepository(Voiture::class)->findBy(['utilisateur' => $user]);
        
        //récuperer les covoiturages validé par l'utilisateur
        $validatedCovoituragesIds =  $documentManager->getRepository(CovoiturageMongo::class)
        ->findBy(['validateUsers' => $user->getId()]);

        /* // Récupérer les objets MongoDB correspondants
        $validatedCovoiturages = [];
        foreach ($validatedCovoituragesIds as $covoiturageId) {
            $covoiturage = $documentManager->getRepository(CovoiturageMongo::class)->find($covoiturageId);
            if ($covoiturage) {
                $validatedCovoiturages[] = $covoiturage;
        } */
        // initialisation des variables:
        $dateAujourdhui = false;
        $isValidateUser = false;
        $avisUserExiste = false;
        $avisUser = null;
        /* $conducteurs = [];  */

        //selectionner les dates futures à la date du jour:
        if ($covoiturages !== null) {
            $now = new \DateTime();
            foreach ($covoiturages as $covoiturage) {

                $conducteurId = (int) $covoiturage->getConducteurId();
                $conducteur = $em->getRepository(Utilisateur::class)->findOneBy(['id' => $conducteurId]);
                
                //verifier si le covoiturage est futur
                $dateFuture = $covoiturage->getDateDepart() > $now;
                $dateFuture = $covoiturage->setDateFuture($dateFuture) ;

                //verifier si le covoiturage est d'aujourd'hui
                $dateAujourdhui = $covoiturage->getDateDepart()->format('d-m-Y') === $now->format('d-m-Y');
                if ($dateAujourdhui){
                    $dateAujourdhui = $covoiturage->setDateAujourdhui();
                }

                /* // Récupérer le conducteur pour chaque covoiturage
        $conducteurId = $covoiturage->getConducteurId();
        if (!is_int($conducteurId)) {
            $conducteurId = (int) $conducteurId; // mettre en int si c'est pas le cas */ 

                 //récuperer l'avis du covoiturage selon le passager
                $avisUser = $em->getRepository(Avis::class)->findOneBy([
                    'passager' => $user,
                    'covoiturage' => $covoiturage
                ]);
                // création donnée si il y a des avis:
                $avisUserExiste = $avisUser !== null;

                
            }

        return $this->render('utilisateur/profil.html.twig', [
            'utilisateur' => $user,
            'commentairesUSer'=> $commentairesUser,
            'voitureUser'=> $voitureUser,
            'covoiturages'=> $covoiturages,
            'dateFuture'=> $dateFuture,
            'id' => $id,
            'observations'=>$observationExplode,
            'dateAujourdhui'=>$dateAujourdhui,
            'avisUserExiste'=>$avisUserExiste,
            'rate'=>$rateUser,
            'isValidateUser'=>$isValidateUser,
            'conducteur'=>$conducteur,
            ]);
        }
    }


    #[Route('/profil/{id}/update', name: 'app_profil_update',requirements: ['id' => '\d+'])]
    
    public function profilUtilisateurUpdate(
    int $id,
    EntityManagerInterface $em,
    Security $security,
    Request $request): Response
    {
        $user = $security->getUser(); // Récupérer l'utilisateur connecté
        
        /* $user = $em->getRepository(Utilisateur::class)->find($id); */

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        // Récuperation des données:
        $observations = $user->getObservation();
        $voitureUser = $em->getRepository(Voiture::class)->findBy(['utilisateur' => $user]);
        
        //création form
        $form = $this->createForm(ProfilFormType::class,$user);
    
        // Gérer la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
        
            // Persister les modifications
            $em->flush();

            $this->addFlash('success', 'Votre profil a bien été modifié .');
        
            // Rediriger après la sauvegarde
            return $this->redirectToRoute('app_profil', ['id' => $user->getId()]);
        }

        // Afficher le formulaire
        return $this->render('utilisateur/update.html.twig', [
            'form' => $form->createView(),
            'utilisateurs' => $user,
            'voitureUser'=> $voitureUser,
            'observations'=>$observations,
        ]);
    }
    
}
