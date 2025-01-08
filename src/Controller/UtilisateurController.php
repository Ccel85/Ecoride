<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Voiture;
use App\Entity\Covoiturage;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\SecurityBundle\Security;
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
    public function archiveUtilisateur(int $id,EntityManagerInterface $em,Security $security): Response
    {
        
        $repository = $em->getRepository(Utilisateur::class);
        $user = $repository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        $user->setActif(false);
        $em->flush();

        /* Rediriger vers la liste des utilisateurs
         // Récupérer l'utilisateur connecté
        $utilisateur = $security->getUser();

        // Vérifier si l'utilisateur a des rôles
        if ($utilisateur) {
            $roles = $utilisateur->getRoles(); // Récupère tous les rôles de l'utilisateur

            // Vérifier les rôles et rediriger en fonction
            if (in_array('ROLE_ADMIN', $roles)) {
                // Si l'utilisateur est un administrateur, redirigez vers la page admin
                return $this->redirectToRoute('app_employe');
            } elseif (in_array('ROLE_EMPLOYE', $roles)) {
                // Si l'utilisateur est un éditeur, redirigez vers la page de l'éditeur
                return $this->redirectToRoute('app_utilisateur');
            } else {
                // Sinon, redirigez vers la page par défaut (par exemple, l'accueil)
                return $this->redirectToRoute('app_home');
            }
        }*/

        // Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
        return $this->redirectToRoute('app_utilisateur');
    }


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

         // Rediriger vers la liste des utilisateurs
    return $this->redirectToRoute('app_utilisateur');
    }

    #[Route('/profil', name: 'app_profil')]
    public function profilUtilisateur(Security $security,EntityManagerInterface $em,): Response
    {
        $utilisateur = $security->getUser(); // Récupérer l'utilisateur connecté
        $commentsUser = $em->getRepository(Avis::class)->findBy(['utilisateur' => $utilisateur]);
        $voitureUser = $em->getRepository(Voiture::class)->findBy(['utilisateur' => $utilisateur]);
        $covoiturages = $utilisateur->getCovoiturage();
        
        if (!$utilisateur) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }
        return $this->render('utilisateur/profil.html.twig', [
            'utilisateurs' => $utilisateur,
            'commentairesUSers'=> $commentsUser,
            'voitureUser'=> $voitureUser,
            'covoiturages'=> $covoiturages
        ]);
    }

    #[Route('/profil/{id}', name: 'app_profil_id')]
    public function profil(int $id,EntityManagerInterface $em): Response
    {
        $repository = $em->getRepository(Utilisateur::class);
        $user = $repository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        return $this->render('utilisateur/profil.html.twig', [
            'utilisateurs' => $user,
        ]);
    }
}

