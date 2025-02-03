<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Voiture;
use App\Entity\Covoiturage;
use App\Entity\Utilisateur;
use App\Form\ProfilFormType;
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
    public function indexUtilisateur(UtilisateurRepository $utilisateurRepository): Response
    {
        $filterUsers = $utilisateurRepository->findByRole('ROLE_USER');
        return $this->render('utilisateur/index.html.twig', [
            'utilisateurs' => $filterUsers,
        ]);
    }

    #[Route('/employe', name: 'app_employe')]
    public function indexEmploye(UtilisateurRepository $utilisateurRepository): Response
    {
        $filterUsers = $utilisateurRepository->findByRole('ROLE_EMPLOYE');

        return $this->render('utilisateur/index.html.twig', [
            'utilisateurs' => $filterUsers,
        ]);
    }

    #[Route('/utilisateur/{id}/archive', name: 'app_utilisateur_archive')]
    public function archiveUtilisateur(int $id,EntityManagerInterface $em): Response
    {
        
        $repository = $em->getRepository(Utilisateur::class);
        $user = $repository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        $user->setActif(false);
        $em->flush();

        $this->addFlash('success', 'L\'archivage a été effectué pour les profils concernés .');

        // Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
        return $this->redirectToRoute('app_utilisateur');
    }

    //Rendre utilisateur actif
    #[Route('/utilisateur/{id}/active', name: 'app_utilisateur_active')]
    
    public function activeUtilisateur(int $id,EntityManagerInterface $em): Response
    {
        
        $repository = $em->getRepository(Utilisateur::class);
        $user = $repository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        $user->setActif(true);
        $em->flush();

        $this->addFlash('success', 'L\'activation a été effectué pour les profils concernés .');

         // Rediriger vers la liste des utilisateurs
    return $this->redirectToRoute('app_utilisateur');
    }

    //Affichage profil connecté
    #[Route('/profil', name: 'app_profil')]
    
    public function profilUtilisateur(Security $security,EntityManagerInterface $em): Response
    {
        $utilisateur = $security->getUser(); // Récupérer l'utilisateur connecté

        if (!$utilisateur) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        // Récuperation des données:
        $commentairesUser = $em->getRepository(Avis::class)->findCommentairesByUserOrdered($utilisateur);
     /*    $commentsUser = $em->getRepository(Avis::class)->findBy(['utilisateur' => $utilisateur]); */
        $voitureUser = $em->getRepository(Voiture::class)->findBy(['utilisateur' => $utilisateur]);
        $covoiturages = $utilisateur->getCovoiturage();
        $validatedCovoiturages = $utilisateur->getValidateCovoiturages($covoiturages);// Affiche tous les covoiturages validés par l'utilisateur
        $observations = $utilisateur->getObservation();
        $rateUser =round($em->getRepository(Avis::class)->rateUser($utilisateur),1);
        // Scinder le texte par les virgules
        $observationExplode = explode(',' ,$observations);
        
        //Verification si Chauffeur qu'un véhicule soit enregistré
        if ($utilisateur->isConducteur(true) && !$voitureUser){
            $this->addFlash('warning', 'Vous êtes un conducteur, vous devez ajouter un véhicule !');
            return $this->redirectToRoute('app_voiture_new');
        }
        //selectionner les dates futures à la date du jour
        if ($covoiturages !== null) {
            foreach ($covoiturages as $covoiturage) {
                $now = new \DateTime();
                $dateFuture = $covoiturage->getDateDepart() > $now;
                $dateFuture = $covoiturage->setDateFuture($dateFuture) ;
                dump($dateFuture);
                $dateAujourdhui = $covoiturage->getDateDepart()->format('Y-m-d') === $now->format('Y-m-d');
                if ($dateAujourdhui){
                    $dateAujourdhui = $covoiturage->setDateAujourdhui();
                }
                dump($dateAujourdhui);
                /*  $isValidate = $utilisateur->getValidateCovoiturages()->contains($covoiturage );*/
                // Affiche tous les covoiturages validés par l'utilisateur
                 // Vérifie si le covoiturage en question est validé
                $isValidate= false;
                if ($isValidate = $validatedCovoiturages->contains($covoiturage)){
                $isValidate = true;
                break;
                /*  dump($validatedCovoiturages);
                dump($isValidate);
                die(); */
            }
            
    }

        return $this->render('utilisateur/profil.html.twig', [
            'utilisateur' => $utilisateur,
            'commentairesUSer'=> $commentairesUser,
            'voitureUser'=> $voitureUser,
            'covoiturages'=> $covoiturages,
            'observations'=>$observationExplode,
            'dateAujourdhui'=>$dateAujourdhui,
            'isValidate'=>$isValidate,
            'rate'=>$rateUser,
        ]);
    }
}

    //affichage profil selon Id
    #[Route('/profil/{id}', name: 'app_profil_id')]
    public function profil(int $id,EntityManagerInterface $em): Response
    {
        $utilisateur = $em->getRepository(Utilisateur::class)->find($id);
        
        if (!$utilisateur) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }
        
        // Récuperation des données:
        $covoiturages = $utilisateur->getCovoiturage();
        $observations = $utilisateur->getObservation();
        /* $avisValide = $em->getRepository(Avis::class)->findBy(['isValid'=>1]); */
        $commentairesUser = $em->getRepository(Avis::class)->findCommentairesByUserOrdered($utilisateur);
        $rateUser =round($em->getRepository(Avis::class)->rateUser($utilisateur),1);
        /* $isValidRate = $em->getRepository(Avis::class)->getIsValid(); */
        $voitureUser = $em->getRepository(Voiture::class)->findBy(['utilisateur' => $utilisateur]);
        $validatedCovoiturages = $utilisateur->getValidateCovoiturages($covoiturages);// Affiche tous les covoiturages validés par l'utilisateur
        /* $commentsUser = $em->getRepository(Avis::class)->findBy(['utilisateur' => $utilisateur]); */
        
        /*  if (!$voitureUser && $utilisateur->isConducteur(true)){
            // Rediriger a la création de vehicule
            $this->addFlash('danger', 'Veuillez ajouter un véhicule');
            return $this->redirectToRoute('app_voiture_new');
            } */
        
           // Scinder le texte par les virgules
            $observationExplode = explode(',' ,$observations);
            
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
                $isValidate= false;
                if ($isValidate = $validatedCovoiturages->contains($covoiturage)){
                $isValidate = true;
                break;
                /*  dump($validatedCovoiturages);
                dump($isValidate);
                die(); */
            }
        }
        return $this->render('utilisateur/profil.html.twig', [
            'utilisateur' => $utilisateur,
            'commentairesUSer'=> $commentairesUser,
            'voitureUser'=> $voitureUser,
            'covoiturages'=> $covoiturages,
            'dateFuture'=> $dateFuture,
            'id' => $id, // Envoie l'ID à la vue
            'observations'=>$observationExplode,
            'dateAujourdhui'=>$dateAujourdhui,
            'isValidate'=>$isValidate,
            'rate'=>$rateUser,
        ]);
    }

    #[Route('/profil/{id}/update', name: 'app_profil_update')]
    
    public function profilUtilisateurUpdate(int $id,Security $security,EntityManagerInterface $em,Request $request): Response
    {
        //$utilisateur = $security->getUser(); // Récupérer l'utilisateur connecté

        $utilisateur = $em->getRepository(Utilisateur::class)->find($id);

        if (!$utilisateur) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        // Récuperation des données:
        $observations = $utilisateur->getObservation();
        $voitureUser = $em->getRepository(Voiture::class)->findBy(['utilisateur' => $utilisateur]);
        /* $rateUser = $em->getRepository(Avis::class)->rateUser(['utilisateur'=>$utilisateur]); */
        //création form
        $form = $this->createForm(ProfilFormType::class,$utilisateur);
    
        // Gérer la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $isConducteur = $form->get('isConducteur')->getData();

            if ($isConducteur === 'Conducteur') {
    
                $utilisateur->setConducteur(true);
                $utilisateur->setPassager(false);
    
            }elseif ($isConducteur === 'Passager') {
                $utilisateur->setConducteur(false);
                $utilisateur->setPassager(true);
    
            }elseif ($isConducteur === 'Conducteur et passager') {
                $utilisateur->setConducteur(true);
                $utilisateur->setPassager(true);
            }

            // Persister les modifications
            $em->flush();

            $this->addFlash('success', 'Votre profil a bien été modifié .');
        
            // Rediriger après la sauvegarde
            return $this->redirectToRoute('app_profil', ['id' => $utilisateur->getId()]);
        }

        // Afficher le formulaire
        return $this->render('utilisateur/update.html.twig', [
            'form' => $form->createView(),
            'utilisateurs' => $utilisateur,
            'voitureUser'=> $voitureUser,
            'observations'=>$observations,
            /* 'rateUser'=>$rateUser, */
        ]);
    }
    
}
