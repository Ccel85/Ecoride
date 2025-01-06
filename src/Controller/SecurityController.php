<?php

namespace App\Controller;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils,Security $security): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

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
                return $this->redirectToRoute('app_utilisateur');

            } else {

                // Sinon, redirection vers page d'accueil
                return $this->redirectToRoute('app_home');
            }
        }
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

        /* #[Route(path: '/connect', name: 'app_connect')]

    public function connect(Security $security): Response
    {
        $utilisateur = $security->getUser();

        // Vérifier si l'utilisateur a des rôles
        if ($utilisateur) {
            $roles = $utilisateur->getRoles(); // Récupère tous les rôles de l'utilisateur

            // Vérifier les rôles et rediriger en fonction
            if (in_array('ROLE_ADMIN', $roles)) {
                // Si l'utilisateur est un administrateur, redirigez vers la page admin
                return $this->redirectToRoute('app_admin_dashboard');
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
    }*/

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
