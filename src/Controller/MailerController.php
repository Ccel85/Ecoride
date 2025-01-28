<?php

namespace App\Controller;

use App\Entity\Covoiturage;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class MailerController extends AbstractController
{
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
    #[Route ('/email', name:'app_email')]

    public function email():Response
    {
        return $this->render('mailer/index.html.twig', [
            
        ]);
    }
}

