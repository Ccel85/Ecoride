<?php

namespace App\Controller;

use App\Entity\Covoiturage;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class MailerController extends AbstractController
{
    //envoi mail suite stop voyage
    #[Route('/sendEmail/{id}', name: 'app_send_email')]
public function sendEmail(int $id,MailerInterface $mailer,EntityManagerInterface $em): Response
{
    try {
        
        $covoiturage= $em->getRepository(Covoiturage::class)->find($id);

        if (!$covoiturage) {
            throw $this->createNotFoundException('Covoiturage introuvable.');
        }

        foreach ($covoiturage->getUtilisateurs() as $utilisateur) {

        $userEmail = $utilisateur->getEmail();

        $email = (new TemplatedEmail())
            ->from('hello@example.com')
            ->to($userEmail)
            ->subject('Ecoride:Vous êtes arrivé!')
            ->htmlTemplate('email/index.html.twig');

        $mailer->send($email);
        }
        
        $this->addFlash('success', 'Email envoyé avec succès !');

    } catch (TransportExceptionInterface $e) {
        $this->addFlash('error', 'Échec de l\'envoi de l\'email : ' . $e->getMessage());
    }

    // Redirection après l'envoi
    $this->addFlash('success', 'Email envoyé avec succès !');
    return $this->redirectToRoute('app_profil'); // Remplacez 'home' par la route souhaitée
}
    //envoi mail lors de la suppression d'un voyage
#[Route('/sendEmail/{id}/remove', name: 'app_send_email_remove')]
public function sendEmailRemove(int $id,Security $security,MailerInterface $mailer,SessionInterface $session ): Response// 🔹 Injecter la session
{
    try {
        $user = $security->getUser();
        if (!$user) {
            $this->addFlash('error', 'Utilisateur non connecté.');
            return $this->redirectToRoute('app_login');
        }

        // 🔹 Récupérer les emails stockés en session
        $emails = $session->get('emails_utilisateurs', []);

        if (empty($emails)) {
            $this->addFlash('error', 'Aucun email trouvé pour ce covoiturage.');
            return $this->redirectToRoute('app_home');
        }

        $userEmail = $user->getEmail();

        // 🔹 Envoyer un email à chaque utilisateur
        foreach ($emails as $email) {
            $message = (new TemplatedEmail())
                ->from($userEmail)
                ->to($email)
                ->subject('Ecoride: Votre covoiturage a été annulé!')
                ->htmlTemplate('email/remove.html.twig');

            $mailer->send($message);
        }

        $this->addFlash('success', 'Emails envoyés avec succès !');
        
    } catch (TransportExceptionInterface $e) {
        $this->addFlash('error', 'Échec de l\'envoi de l\'email : ' . $e->getMessage());
    }

    return $this->redirectToRoute('app_profil');
}

    #[Route ('/email', name:'app_email')]

    public function email():Response
    {
        return $this->render('mailer/index.html.twig', [
            
        ]);
    }
}

