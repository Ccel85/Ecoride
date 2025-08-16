<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Form\AvisFormType;
use App\Entity\Utilisateur;
use App\Document\CovoiturageMongo;
use App\Repository\AvisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class AvisController extends AbstractController{

//avis
#[Route('/avis', name: 'app_avis')]

    public function index(AvisRepository $avisRepositpory): Response
    {
        $avis = $avisRepositpory->findall();//collection d'avis
        $invalidComments = $avisRepositpory->invalidComments();
        $passagers = [];

        //on recupere chaque passager pour chaque avis 
        foreach ($avis as $a) {
            $passagers[] = $a->getPassager();
        }

        return $this->render('avis/listeAvis.html.twig', [
            'avis'=>$avis,
            'invalidComments'=>$invalidComments,
            'passager'=>$passagers,
        ]);
    }
//signalement
#[Route('/signalement', name: 'app_signalement')]

    public function signalement(AvisRepository $avisRepositpory): Response
    {
        $avis = $avisRepositpory->findall();
        $signalComments = $avisRepositpory->signalComments();

        return $this->render('avis/listeSignalement.html.twig', [
            'avis'=>$avis,
            'signalComments'=>$signalComments,
        ]);
    }

//Créer un avis
#[Route('/avis/{id}/new', name: 'app_avis_new', requirements: ['id' => '.+'])]

    public function avisNew(
    string $id,
    Request $request,
    EntityManagerInterface $em,
    DocumentManager $documentManager,
    Security $security): Response
    {
        $user = $security->getUser();

        if (!$user) {
            $this->addFlash('warning', 'Veuillez vous connecter ou créer un compte.');
            return $this->redirectToRoute('app_login');
        }

        $covoiturage = $documentManager->getRepository(CovoiturageMongo::class)->find($id);

        if (!$covoiturage) {
            throw $this->createNotFoundException('Covoiturage non trouvé.');
        }

        $conducteurId = $covoiturage->getConducteurId();

        $conducteur = $em->getrepository(Utilisateur::class)->find($conducteurId);

            $avis = new Avis();

            $form = $this->createForm(AvisFormType::class, $avis,);
        
            $form->handleRequest($request);
        
            if ($form->isSubmitted() && $form->isValid()) {
            
                $avis->setConducteur($conducteur);
                $avis->setCovoiturage($id);
                $avis->setPassager($user);
                
                $em->persist($avis);
                $em->flush();
            
                $this->addFlash('success', 'Votre avis est enregistré.');
                return $this->redirectToRoute('app_profil');
            }
            return $this->render('avis/new.html.twig', [
                'form' => $form->createView(),
                'covoiturage'=>$covoiturage
            ]);
    }
//signaler un avis
#[Route('/avis/{id}/signaler', name: 'app_avis_signaler', requirements: ['id' => '.+'])]

    public function avisSignaler(
    string $id,
    Request $request,
    EntityManagerInterface $em,
    DocumentManager $documentManager,
    Security $security): Response
    {
        $user = $security->getUser();

       /*  $rate = $request->query->get('rate'); */

        if (!$user) {
            $this->addFlash('warning', 'Veuillez vous connecter ou créer un compte.');
            return $this->redirectToRoute('app_login');
        }
        $covoiturage = $documentManager->getRepository(CovoiturageMongo::class)->find($id);
        //$covoiturage = $em->getRepository(CovoiturageMongo::class)->find($id);

        if (!$covoiturage) {
            throw $this->createNotFoundException('Covoiturage non trouvé.');
        }

        $conducteurId = $covoiturage->getConducteurId();

        $conducteur = $em->getrepository(Utilisateur::class)->find($conducteurId);

            $avis = new Avis();

            $form = $this->createForm(AvisFormType::class, $avis,);
        
            $form->handleRequest($request);
        
            if ($form->isSubmitted() && $form->isValid()) {
            
                $avis->setConducteur($conducteur);

                $avis->setIsSignal(true);

                $avis->setPassager($user);

                $avis->setCovoiturage($id);
            
                
                $em->persist($avis);
                $em->flush();
            
                $this->addFlash('success', 'Votre avis est enregistré.');
                return $this->redirectToRoute('app_profil');
            }
            return $this->render('avis/signalement.html.twig', [
                'form' => $form->createView(),
                'conducteur'=> $conducteur,
            ]);
    }

//Valider un avis
#[Route('/avis/update', name: 'app_avis_update' )]

    public function avisUpdate(AvisRepository $avisRepository,
    Request $request,
    EntityManagerInterface $em,
    MailerInterface $mailer,
    Security $security): Response
    {
        $utilisateur = $security->getUser();

        // Vérifier si l'utilisateur est connecté
        if (!$utilisateur) {
            $this->addFlash('warning', 'Veuillez vous connecter ou créer un compte.');
            return $this->redirectToRoute('app_login');
        }
        // Récupérer tous les avis invalides
            $selectedIds = $request->request->all('isValid', []);
        // Récupérer les emails des utilisateurs participants
            $emails = [];
        
        if (!empty($selectedIds)) {
            $invalidAvis = $avisRepository->findBy(['id' => $selectedIds]);
            //si le bouton archive est selectionner:
            if ($request->request->has('isValid')) {
                foreach ($invalidAvis as $avis) {
                    $avis->setValid(true);
                    $em->persist($avis);
                    $passager = $avis->getPassager();
            //$passager = $entityManager->getRepository(Utilisateur::class)->find($passagerId);
            if ($passager && $passager->getEmail()) {
                $emails[] = $passager->getEmail();
            }
        }
        // Envoyer un email à chaque utilisateur
        foreach ($emails as $email) {
            $emailUser = (new TemplatedEmail())
            ->from('hello@example.com')
            ->to($email)
            ->subject('Ecoride:Votre avis est validé!')
            ->htmlTemplate('email/avisValide.html.twig');

            $mailer->send($emailUser);
        }
            }
            $em->flush();
            $this->addFlash('success', 'Avis validé avec succès !,Email envoyé!');
                
            } else {
                $this->addFlash('warning', 'Aucun avis sélectionné.');
            }
            return $this->redirectToRoute('app_employe_dashboard');
            }
        
//Supprimer un avis
#[Route('/avis/{id}/remove', name: 'app_avis_remove', requirements: ['id' => '.+'] )]

    public function avisRemove(int $id,EntityManagerInterface $em,Security $security): Response 
    {

        $utilisateur = $security->getUser();
        $deleteAvis = $em->getRepository(Avis::class)->find($id);

        // Vérifier si l'utilisateur est connecté
        if (!$utilisateur) {
            $this->addFlash('warning', 'Veuillez vous connecter ou créer un compte.');
            return $this->redirectToRoute('app_login');

            } else {

            $em->remove($deleteAvis);
            $em->flush();

            $this->addFlash('success', 'L\'avis a été supprimé.');
            return $this->redirectToRoute('app_employe_dashboard');

        }
    }

//Avis details
#[Route('/avis/{id}/detail', name: 'app_avis_detail', requirements: ['id' => '.+'] )]

    public function détailAvis(
    EntityManagerInterface $em,
    DocumentManager $dm,
    int $id): Response
    {
        $avisRepository = $em->getRepository(Avis::class);
        $avis = $avisRepository->find($id);

        $covoiturageId = $avis->getCovoiturage();
        $covoiturage = $dm->find(CovoiturageMongo::class,  $covoiturageId);

        if (!$covoiturage) {
            throw $this->createNotFoundException("Le covoiturage n'existe pas.");
        }
        // Récupération des informations liées
        $conducteur = $avis->getConducteur();//conducteur lié à l'avis;
        
        $avisValide = $avisRepository->findOneBy([
            'conducteur' => $conducteur,
            'isValid' => true,
        ]);
        if ($avisValide) {
            // Le conducteur a au moins un avis valide
            $avisExiste = true;
        } else {
            // Aucun avis valide trouvé
            $avisExiste = false;
        }
        $passager = $avis->getPassager();//passager lié à l'avis;

        //note moyenne du conducteur
        $note = $em->getRepository(Avis::class)->rateUser($conducteur);
        $rateUser =$note !== null ? round($note,1): null;
        //$rateUser =round($em->getRepository(Avis::class)->rateUser($conducteur),1);

        //récuperation des avis lié au conducteur
        $commentsUser = $em->getRepository(Avis::class)->findBy(['conducteur' => $conducteur]);

        return $this->render('avis/detail.html.twig', [
            'covoiturage'=>$covoiturage,
            'conducteur'=>$conducteur,
            'commentaires'=>$commentsUser,
            'passager'=>$passager,
            'rateUser'=>$rateUser,
            'avis'=>$avis,
            'avisExiste'=>$avisExiste
        ]);
    }
}

