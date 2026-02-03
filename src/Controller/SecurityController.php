<?php

namespace App\Controller;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
        public function login(
            AuthenticationUtils $authenticationUtils,
            Security $security): Response
        {
        //recuperer le login erreur
        $error = $authenticationUtils->getLastAuthenticationError();
        // recuperer le dernier nom entré
        $lastUsername = $authenticationUtils->getLastUsername();
        //gérer les erreurs
        if ($error instanceof BadCredentialsException){
            $errorMessage = "Adresse email ou mot de passe incorrect.";
        } else {
            $errorMessage = null;
        }

        $utilisateur = $security->getUser();
        
        if ($utilisateur === null) {
            // Si aucun utilisateur n'est connecté, afficher la page de connexion
            return $this->render('security/login.html.twig', [
                'last_username' => $lastUsername,
                'error' => $error,
            ]);
        }
        // Vérifier si l'utilisateur à des rôles et redirection:
        if ($utilisateur) {
            $roles = $utilisateur->getRoles(); // Récupère tous les rôles de l'utilisateur

            // Vérifier les rôles et rediriger en fonction
            if (in_array("ROLE_ADMIN", $roles,true)) {

                // Si l'utilisateur est un administrateur, redirigez vers la page admin
                return $this->redirectToRoute('app_admin_dashboard');

            } elseif (in_array("ROLE_EMPLOYE", $roles,true)) {

                // Si l'utilisateur est un employé, redirigez vers la page employé
                return $this->redirectToRoute('app_employe_dashboard');

            } else {

                // Sinon, redirection vers page d'accueil
                return $this->redirectToRoute('app_home');
            }
        }
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $errorMessage,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
