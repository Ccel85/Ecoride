<?php

namespace App\Controller;

use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class MailerController extends AbstractController
{
    #[Route('/email', name: 'app_send_email')]
public function sendEmail(MailerInterface $mailer): Response
{
    try {
        $email = (new TemplatedEmail())
            ->from('ccelena90@gmail.com')
            ->to('ccelena@neuf.fr')
            ->subject('Time for Symfony Mailer!')
            ->htmlTemplate('mailer/index.html.twig')
            ->context([
                'expiration_date' => new \DateTime('+7 days'),
                'username' => 'foo',
            ]);

        $mailer->send($email);

        $this->addFlash('success', 'Email envoyé avec succès !');

    } catch (TransportExceptionInterface $e) {
        $this->addFlash('error', 'Échec de l\'envoi de l\'email : ' . $e->getMessage());
    }
    /* return new Response('<p>Email sent successfully!</p>'); */

    // Redirection après l'envoi
    return $this->redirectToRoute('app_profil'); // Remplacez 'home' par la route souhaitée
}


}

