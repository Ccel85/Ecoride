<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Voiture;
use App\Entity\Utilisateur;
use App\Form\ProfilFormType;
use App\Repository\AvisRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UtilisateurController extends AbstractController
{
    #[Route('/utilisateur', name: 'app_utilisateur')]
    public function indexUtilisateur(Security $security,UtilisateurRepository $utilisateurRepository,EntityManagerInterface $em): Response
    {
        $utilisateurs = $utilisateurRepository->findByRole("ROLE_USER");
        $user = $security->getUser();

        $rates = [];
        foreach ($utilisateurs as $utilisateur) {
        $rates [$utilisateur->getId()] =round($em->getRepository(Avis::class)->rateUser($utilisateur),1);
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
    public function archiveUtilisateur(int $id,EntityManagerInterface $em,Security $security): Response
    {
        
        $repository = $em->getRepository(Utilisateur::class);
        $utilisateur = $repository->find($id);
        $user = $security->getUser();
        $roleUser = $user->getRoles();

        if (!$utilisateur) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        $utilisateur->setActif(false);
        $em->flush();

        $this->addFlash('success', 'L\'archivage a été effectué pour le profil concerné .');

        if ($roleUser == ['ROLE_ADMIN']){

            return $this->redirectToRoute('app_employe');

        } else {

            return $this->redirectToRoute('app_utilisateur');

        }
    }

    //Rendre utilisateur actif
    #[Route('/utilisateur/{id}/active', name: 'app_utilisateur_active', requirements: ['id' => '\d+'])]
    
    public function activeUtilisateur(int $id,EntityManagerInterface $em,Security $security): Response
    {
        
        $repository = $em->getRepository(Utilisateur::class);
        $utilisateur = $repository->find($id);
        $user = $security->getUser();
        $roleUser = $user->getRoles();


        if (!$utilisateur) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        $utilisateur->setActif(true);
        $em->flush();

        $this->addFlash('success', 'L\'activation a été effectué pour le profil concerné.');

        if ($roleUser == ['ROLE_ADMIN']){

            return $this->redirectToRoute('app_employe');

        } else {

            return $this->redirectToRoute('app_utilisateur');

        }
    }

    //Affichage profil connecté
    #[Route('/profil', name: 'app_profil')]
    
    public function profilUtilisateur(Security $security,EntityManagerInterface $em,AvisRepository $avisRepository): Response
    {
        $user = $security->getUser(); // Récupérer l'utilisateur connecté

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        // Récuperation des données:
        $commentairesUser = $em->getRepository(Avis::class)->findCommentairesByUserOrdered($user);
        $voitureUser = $em->getRepository(Voiture::class)->findBy(['utilisateur' => $user]);
        $covoiturages = $user->getCovoiturage();
        $validatedCovoiturages = $user->getValidateCovoiturages($covoiturages);// Affiche tous les covoiturages validés par l'utilisateur
        $observations = $user->getObservation();
        $rateUser =round($em->getRepository(Avis::class)->rateUser($user),1);

        // Scinder le texte par les virgules
        $observationExplode = explode(',' ,$observations);
        
        //Verification si Chauffeur qu'un véhicule soit enregistré
        if ($user->isConducteur(true) && !$voitureUser){
            $this->addFlash('warning', 'Vous êtes un conducteur, vous devez ajouter un véhicule !');
            return $this->redirectToRoute('app_voiture_new');
        }

        $dateAujourdhui = false;
        $isValidateUser = false;
        /* $signalComment = null; */
        $avisUserExiste = false;
        $avisUser = null;

        //selectionner les dates futures à la date du jour
        if ($covoiturages !== null) {
            foreach ($covoiturages as $covoiturage) {
                $now = new \DateTime();
                $dateFuture = $covoiturage->getDateDepart() > $now;
                $dateFuture = $covoiturage->setDateFuture($dateFuture) ;
                
                $dateAujourdhui = $covoiturage->getDateDepart()->format('d-m-Y') === $now->format('d-m-Y');
                if ($dateAujourdhui){
                    $dateAujourdhui = $covoiturage->setDateAujourdhui($dateAujourdhui);
                }
                /*  $isValidate = $utilisateur->getValidateCovoiturages()->contains($covoiturage );*/
                // Affiche tous les covoiturages validés par l'utilisateur
                 // Vérifie si le covoiturage en question est validé
                if ($isValidateUser = $validatedCovoiturages->contains($covoiturage)){
                $isValidateUser = true;
                break;
                }
                
            }
        }

        $avisUser = $avisRepository->findOneBy([
            'passager' => $user,
            'covoiturage' => $covoiturage
        ]);
        
        $avisUserExiste = $avisUser !== null;
/* 
        $signalComment = $avisRepository->findOneBy([
            'isSignal' => true,
            'passager' => $utilisateur,
            'covoiturage' => $covoiturage
        ]); */
        /* dump($utilisateur); // Vérifie que l'utilisateur est bien récupéré
        dump($covoiturage); // Vérifie que le covoiturage existe
        dump($avisRepository->findBy(['passager' => $utilisateur])); // Vérifie s'il y a des avis pour cet utilisateur
        dump($avisRepository->findBy(['covoiturage' => $covoiturage])); // Vérifie s'il y a des avis pour ce covoiturage
        die(); */
       /*  dump($avisUser, $avisUserExiste);
die(); */ //  Arrête l'exécution pour voir le résultat

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
                ]);
        }
    

    //affichage profil selon Id
    #[Route('/profil/{id}', name: 'app_profil_id' ,requirements: ['id' => '\d+'])]
    public function profil(int $id,EntityManagerInterface $em,AvisRepository $avisRepository): Response
    {
        $user = $em->getRepository(Utilisateur::class)->find($id);
        
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }
        
        // Récuperation des données:
        $covoiturages = $user->getCovoiturage();
        $observations = $user->getObservation();
        $commentairesUser = $em->getRepository(Avis::class)->findCommentairesByUserOrdered($user);
        $rateUser =round($em->getRepository(Avis::class)->rateUser($user),1);
        $voitureUser = $em->getRepository(Voiture::class)->findBy(['utilisateur' => $user]);
        $validatedCovoiturages = $user->getValidateCovoiturages($covoiturages);// Affiche tous les covoiturages validés par l'utilisateur
        
        
        /*  if (!$voitureUser && $utilisateur->isConducteur(true)){
            // Rediriger a la création de vehicule
            $this->addFlash('danger', 'Veuillez ajouter un véhicule');
            return $this->redirectToRoute('app_voiture_new');
            } */
        
           // Scinder le texte par les virgules
            $observationExplode = explode(',' ,$observations);
            

            $dateAujourdhui = false;
            $isValidateUser = false;
            /* $signalComment = null; */
            $avisUserExiste = false;
            $avisUser = null;

        //selectionner les dates futures à la date du jour
        if ($covoiturages !== null) {
            //selectionner les dates futures à la date du jour
            foreach ($covoiturages as $covoiturage) {
                $now = new \DateTime();
                $dateFuture = $covoiturage->getDateDepart() > $now;
                $dateFuture = $covoiturage->setDateFuture($dateFuture) ;
                /*  dump($dateFuture);
                dump($now); 
                die; */
                $dateAujourdhui = $covoiturage->getDateDepart()->format('d-m-Y') === $now->format('d-m-Y');
                /* dump($dateAujourdhui) ;
                    die;*/
                if ($dateAujourdhui){
                    $dateAujourdhui = $covoiturage->setDateAujourdhui();
                }
                
            /*  $isValidate = $utilisateur->getValidateCovoiturages()->contains($covoiturage );*/
                // Affiche tous les covoiturages validés par l'utilisateur
                 // Vérifie si le covoiturage en question est validé
                if ($isValidateUser = $validatedCovoiturages->contains($covoiturage)){
                    $isValidateUser = true;
                    break;
                    }
                /*  dump($validatedCovoiturages);
                dump($isValidate);
                die(); */
            }
        }

                $avisUser = $avisRepository->findOneBy([
                    'passager' => $user,
                    'covoiturage' => $covoiturage
                ]);
    
                $avisUserExiste = $avisUser !== null;

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
        ]);
    }

    #[Route('/profil/{id}/update', name: 'app_profil_update')]
    
    public function profilUtilisateurUpdate(int $id,Security $security,EntityManagerInterface $em,Request $request): Response
    {
        //$utilisateur = $security->getUser(); // Récupérer l'utilisateur connecté

        $user = $em->getRepository(Utilisateur::class)->find($id);

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
            $isConducteur = $form->get('isConducteur')->getData();

            if ($isConducteur === 'Conducteur') {
    
                $user->setConducteur(true);
                $user->setPassager(false);
    
            }elseif ($isConducteur === 'Passager') {
                $user->setConducteur(false);
                $user->setPassager(true);
    
            }elseif ($isConducteur === 'Conducteur et passager') {
                $user->setConducteur(true);
                $user->setPassager(true);
            }

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
