<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManager;
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

         // Rediriger vers la liste des utilisateurs
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
        }

        // Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
        return $this->redirectToRoute('app_login');
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
}

