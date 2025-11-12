<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Symfony\Component\Mime\Email;
use App\Document\CovoiturageMongo;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class MailerController extends AbstractController
{
//envoi mail suite stop voyage
#[Route('/sendEmail/{id}', name: 'app_send_email')]
    public function sendEmail(
    string $id,
    MailerInterface $mailer,
    UrlGeneratorInterface $urlGenerator,
    DocumentManager $documentManager ,
    EntityManagerInterface $entityManager): Response
    {
    try {
        
        $covoiturage= $documentManager->getRepository(CovoiturageMongo::class)->find($id);

        if (!$covoiturage) {
            throw $this->createNotFoundException('Covoiturage introuvable.');
        }
        // Récupérer les emails des utilisateurs participants
        $emails = [];
        foreach ($covoiturage->getPassagersIds() as $passagerId) {
            $passager = $entityManager->getRepository(Utilisateur::class)->find($passagerId);
            if ($passager && $passager->getEmail()) {
                $emails[] = $passager->getEmail();
            }
        }
        //Generer l'adresse URL du covoiturage
        $urlCovoiturage = $urlGenerator->generate('app_covoiturage_id', [
            'id' => $covoiturage->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        // Envoyer un email à chaque utilisateur
        foreach ($emails as $email) {
            $emailUser = (new TemplatedEmail())
            ->from('hello@example.com')
            ->to($email)
            ->subject('Ecoride:Vous êtes arrivé!')
            ->htmlTemplate('email/index.html.twig')
            ->textTemplate('email/index.text.twig')
            ->context([
                'covoiturageUrl' => $urlCovoiturage,
                'passager' =>$passager,
                'covoiturage'=>$covoiturage
            ]);
            $mailer->send($emailUser);
        }
        $this->addFlash('success', 'Un email est envoyé à chaque passager pour déposer un avis.');

        } catch (TransportExceptionInterface $e) {
            $this->addFlash('error', 'Échec de l\'envoi de l\'email : ' . $e->getMessage());
        }
        return $this->redirectToRoute('app_profil'); 
    }
    

//envoi mail lors de la suppression d'un voyage
#[Route('/sendEmail/{id}/remove', name: 'app_send_email_remove')]

    public function sendEmailRemove(
        string $id,
        SessionInterface $session,
        Security $security,
        MailerInterface $mailer,
        UrlGeneratorInterface $urlGenerator
        ): Response
    {
        try {
            //recuperer l'utilisateur connecté
            $user = $security->getUser();
            if (!$user) {
                $this->addFlash('warning', 'Utilisateur non connecté.');
                return $this->redirectToRoute('app_login');
            }
            //$covoiturage = $dm->find(CovoiturageMongo::class, $id);
            //recuperer le covoiturage en session:
            $covoiturage = $session->get('covoiturage');
            $urlCovoiturageRecherche = $urlGenerator->generate('app_covoiturage_recherche',[],
                                        UrlGeneratorInterface::ABSOLUTE_URL);

            //recuperer les email en session:
            $emails = $session->get('emails_utilisateurs', []);
            
            if (empty($emails)) {
                $this->addFlash('error', 'Aucun email trouvé pour ce covoiturage.');
                return $this->redirectToRoute('app_home');
            }
            
            // Envoyer un email à chaque utilisateur
            foreach ($emails as $email) {
            $emailUser = (new TemplatedEmail())
                ->from('ecoride.annulation@mail.com')
                ->to($email)
                ->subject('Ecoride: Votre covoiturage a été annulé!')
                ->htmlTemplate('email/remove.html.twig')
                ->textTemplate('email/remove.txt.twig')
                ->context([
                    'covoiturage'=>$covoiturage,
                    'urlCovoiturageRecherche'=>$urlCovoiturageRecherche
                ]);

                $mailer->send($emailUser);
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
//envoyer mail à la validation d'un avis utilisateur
#[Route('/sendEmailActif/{id}', name: 'app_send_email_actif')]
    public function sendEmailAvisActif(
            string $id,
            MailerInterface $mailer,
            DocumentManager $documentManager ,
            EntityManagerInterface $entityManager): Response
    {
        try {
            
            $covoiturage= $documentManager->getRepository(CovoiturageMongo::class)->find($id);

            if (!$covoiturage) {
                throw $this->createNotFoundException('Covoiturage introuvable.');
            }
            // Récupérer les emails des utilisateurs participants
            $emails = [];
            foreach ($covoiturage->getPassagersIds() as $passagerId) {
                $passagers = $entityManager->getRepository(Utilisateur::class)->find($passagerId);
                if ($passagers && $passagers->getEmail()) {
                    $emails[] = $passagers->getEmail();
                }
            }
            
            // Envoyer un email à chaque utilisateur
            foreach ($emails as $email) {
                $emailUser = (new TemplatedEmail())
                ->from('hello@example.com')
                ->to($email)
                ->subject('Ecoride:votre avis est validé!')
                ->htmlTemplate('email/avisValide.html.twig')
                ->textTemplate('email/avisValide.text.twig');

            $mailer->send($emailUser);
            }
        
            $this->addFlash('success', 'Un email est envoyé à chaque passager pour déposer un avis.');

        } catch (TransportExceptionInterface $e) {
            $this->addFlash('error', 'Échec de l\'envoi de l\'email : ' . $e->getMessage());
        }
        // Redirection après l'envoi

        return $this->redirectToRoute('app_profil'); // Remplacez 'home' par la route souhaitée
    }

//envoyer mail au conducteur à la réservation d'un voyage par passager
#[Route('/sendEmailReservation/{id}', name: 'app_send_email_reservation')]
    public function sendEmailReservation(
            string $id,
            Security $security,
            MailerInterface $mailer,
            UrlGeneratorInterface $urlGenerator,
            DocumentManager $documentManager ,
            EntityManagerInterface $entityManager): Response
    {
        try {

            $user = $security -> getUser();

            if (!$user){
                $this->addFlash('warning','Vous devez être connecté ou créer un compte.');
                return $this->redirectToRoute('app_login');
            }
            
            $covoiturage= $documentManager->getRepository(CovoiturageMongo::class)->find($id);

            if (!$covoiturage) {
                throw $this->createNotFoundException('Covoiturage introuvable.');
            }

            // Récupérer l'email du conducteur
            $conducteurId =(string) $covoiturage->getConducteurId();
            $conducteur = $entityManager-> getRepository(Utilisateur::class) -> findOneBy (['id'=> $conducteurId]);
            $conducteurMail = $conducteur -> getEmail();
            
            //Generer l'adresse URL du covoiturage
            $urlCovoiturage = $urlGenerator->generate('app_covoiturage_id', [
                'id' => $covoiturage->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);

            $urlPassager = $urlGenerator->generate(
                'app_profil_id',
                ['id' => $user->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL);

            $urlValidPassager = $urlGenerator->generate(
                'app_covoiturage_participateValid',
                ['passagerId' => $user->getId(),
                'id' => $covoiturage->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL);

            $urlInvalidPassager = $urlGenerator->generate(
                'app_send_email_invalid_reservation',
                ['id' => $covoiturage->getId(),
                'passagerId' => $user->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL);

            $urlrechercheCovoiturage = $urlGenerator->generate(
                'app_covoiturage',[],UrlGeneratorInterface::ABSOLUTE_URL);

            // Envoyer un email au conducteur
                $email = (new TemplatedEmail())
                ->from('hello@example.com')
                ->to($conducteurMail)
                ->subject('Ecoride:Un nouveau passager pour votre voyage!')
                ->htmlTemplate('email/validationPassager.html.twig')
                //->textTemplate('email/validationPassager.text.twig')
                ->context([
                    'passager'=>$user,
                    'conducteur'=>$conducteur,
                    'covoiturage'=>$covoiturage,
                    'covoiturageUrl' => $urlCovoiturage,
                    'passagerurl' => $urlPassager,
                    'urlValidPassager'=>$urlValidPassager,
                    'urlInvalidPassager'=>$urlInvalidPassager,
                    'urlrechercheCovoiturage'=>$urlrechercheCovoiturage
                ]);

            $mailer->send($email);
        
            $this->addFlash('success', 'Un email est envoyé au conducteur pour validation.
            Vous recevrez un email de confirmation dès la validation effectuée.');

        } catch (TransportExceptionInterface $e) {
            $this->addFlash('error', 'Échec de l\'envoi de l\'email : ' . $e->getMessage());
        }
        // Redirection après l'envoi
        return $this->redirectToRoute('app_profil');
    }
//envoyer mail au passager suite validation conducteur
#[Route('/sendEmailValidReservation/{id}/{passagerId}', name: 'app_send_email_valid_reservation')]
    public function sendEmailValidReservation(
            string $id,
            int $passagerId,
            Security $security,
            MailerInterface $mailer,
            DocumentManager $documentManager ,
            EntityManagerInterface $entityManager): Response
    {
        try {

            $user = $security -> getUser();

            if (!$user){
                $this->addFlash('warning','Vous devez être connecté ou créer un compte.');
                return $this->redirectToRoute('app_login');
            }
            
            $covoiturage= $documentManager->getRepository(CovoiturageMongo::class)->find($id);

            if (!$covoiturage) {
                throw $this->createNotFoundException('Covoiturage introuvable.');
            }

            // Récupérer l'email du passager
            /* $conducteurId =(string) $covoiturage->getConducteurId(); */
            $passager = $entityManager-> getRepository(Utilisateur::class) -> findOneBy (['id'=> $passagerId]);
            $passagerMail = $passager -> getEmail();
            
            // Envoyer un email au passager
                $email = (new TemplatedEmail())
                ->from('hello@example.com')
                ->to($passagerMail)
                ->subject('Ecoride:Votre voyage est validé !')
                ->htmlTemplate('email/voyageConfirme.html.twig')
                //->textTemplate('email/validationPassager.text.twig')
                ->context([
                    'passager'=>$passager,
                    //'conducteur'=>$conducteur,
                    'covoiturage'=>$covoiturage
                ]);

            $mailer->send($email);
        
            $this->addFlash('success', 'Un email est envoyé au passager pour confirmer son voyage.
            ');

        } catch (TransportExceptionInterface $e) {
            $this->addFlash('error', 'Échec de l\'envoi de l\'email : ' . $e->getMessage());
        }
        // Redirection après l'envoi
        return $this->redirectToRoute('app_profil');
    }

    //envoyer mail au passager suite refus conducteur
#[Route('/sendEmailInvalidReservation/{id}/{passagerId}', name: 'app_send_email_invalid_reservation')]
    public function sendEmailInvalidReservation(
            string $id,
            int $passagerId,
            Security $security,
            MailerInterface $mailer,
            UrlGeneratorInterface $urlGenerator,
            DocumentManager $documentManager ,
            EntityManagerInterface $entityManager): Response
    {
        try {

            $user = $security -> getUser();

            if (!$user){
                $this->addFlash('warning','Vous devez être connecté ou créer un compte.');
                return $this->redirectToRoute('app_login');
            }
            
            $covoiturage= $documentManager->getRepository(CovoiturageMongo::class)->find($id);

            if (!$covoiturage) {
                throw $this->createNotFoundException('Covoiturage introuvable.');
            }

            // Récupérer l'email du passager
            /* $conducteurId =(string) $covoiturage->getConducteurId(); */
            $passager = $entityManager-> getRepository(Utilisateur::class) -> findOneBy (['id'=> $passagerId]);
            $passagerMail = $passager -> getEmail();
            
            $urlrechercheCovoiturage = $urlGenerator->generate(
                'app_covoiturage_recherche',[],UrlGeneratorInterface::ABSOLUTE_URL);

            // Envoyer un email au conducteur
                $email = (new TemplatedEmail())
                ->from('hello@example.com')
                ->to($passagerMail)
                ->subject('Ecoride:Votre voyage n\'a pas été validé !')
                ->htmlTemplate('email/voyageAnnulé.html.twig')
                //->textTemplate('email/validationPassager.text.twig')
                ->context([
                    'passager'=>$passager,
                    //'conducteur'=>$conducteur,
                    'covoiturage'=>$covoiturage,
                    'urlrechercheCovoiturage'=>$urlrechercheCovoiturage
                ]);

            $mailer->send($email);
        
            $this->addFlash('success', 'Un email est envoyé au passager pour notifier l\'annulation de sa réservation.
            ');

        } catch (TransportExceptionInterface $e) {
            $this->addFlash('error', 'Échec de l\'envoi de l\'email : ' . $e->getMessage());
        }
        // Redirection après l'envoi
        return $this->redirectToRoute('app_profil');
    }
}

