<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Covoiturage;
use App\Form\AvisFormType;
use App\Repository\AvisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class AvisController extends AbstractController{
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
    #[Route('/avis/new/{id}', name: 'app_avis_new', requirements: ['id' => '\d+'])]

    public function avisNew(int $id,Request $request, EntityManagerInterface $em,Security $security): Response
    {
        $utilisateur = $security->getUser();

       /*  $rate = $request->query->get('rate'); */

        if (!$utilisateur) {
            $this->addFlash('warning', 'Veuillez vous connecter ou créer un compte.');
            return $this->redirectToRoute('app_login');
        }

        $covoiturage = $em->getRepository(Covoiturage::class)->find($id);

        if (!$covoiturage) {
            throw $this->createNotFoundException('Covoiturage non trouvé.');
        }

        $conducteur = $covoiturage->getConducteur();

            $avis = new Avis();

            $form = $this->createForm(AvisFormType::class, $avis,);
        
            $form->handleRequest($request);
        
            if ($form->isSubmitted() && $form->isValid()) {
            
                $avis->setConducteur($conducteur);
            
               /*  $avis->addAvis($utilisateur); */
                
                $em->persist($avis);
                $em->flush();
            
                $this->addFlash('success', 'Votre avis est enregistré.');
                return $this->redirectToRoute('app_profil');
            }
            return $this->render('avis/new.html.twig', [
                'form' => $form->createView(),
                'conducteur'=> $conducteur,
            ]);
    }

    #[Route('/avis/signaler/{id}', name: 'app_avis_signaler', requirements: ['id' => '\d+'])]

    public function avisSignaler(int $id,Request $request, EntityManagerInterface $em,Security $security): Response
    {
        $utilisateur = $security->getUser();

       /*  $rate = $request->query->get('rate'); */

        if (!$utilisateur) {
            $this->addFlash('warning', 'Veuillez vous connecter ou créer un compte.');
            return $this->redirectToRoute('app_login');
        }

        $covoiturage = $em->getRepository(Covoiturage::class)->find($id);

        if (!$covoiturage) {
            throw $this->createNotFoundException('Covoiturage non trouvé.');
        }

        $conducteur = $covoiturage->getConducteur();

            $avis = new Avis();

            $form = $this->createForm(AvisFormType::class, $avis,);
        
            $form->handleRequest($request);
        
            if ($form->isSubmitted() && $form->isValid()) {
            
                $avis->setConducteur($conducteur);

                $avis->setIsSignal(true);

                $avis->setPassager($utilisateur);

                $avis->setCovoiturage($covoiturage);
            
                
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

    public function avisUpdate(AvisRepository $avisRepository,Request $request,EntityManagerInterface $em,Security $security): Response 
    {
        $utilisateur = $security->getUser();

        // Vérifier si l'utilisateur est connecté
        if (!$utilisateur) {
            $this->addFlash('warning', 'Veuillez vous connecter ou créer un compte.');
            return $this->redirectToRoute('app_login');
        }

        // Récupérer tous les avis invalides
        $selectedIds = $request->request->all('isValid', []);

        if (!empty($selectedIds)) {
            $invalidAvis = $avisRepository->findBy(['id' => $selectedIds]);
        //si le bouton archive est selectionner:
            if ($request->request->has('isValid')) {
                foreach ($invalidAvis as $avis) {
                    $avis->setValid(true);
                    $em->persist($avis);
                }
                $em->flush();
                $this->addFlash('success', 'Avis validé avec succès !');
            } else {
                $this->addFlash('warning', 'Aucun avis sélectionné.');
            }
            return $this->redirectToRoute('app_employe_dashboard');
            }
    }

//Supprimer un avis
    #[Route('/avis/{id}/remove', name: 'app_avis_remove', requirements: ['id' => '\d+'] )]

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
    #[Route('/avis/{id}/detail', name: 'app_avis_detail', requirements: ['id' => '\d+'] )]

    public function détailAvis(EntityManagerInterface $em,int $id): Response
    
    {
        $avis = $em->getRepository(Avis::class)->find($id);

        $covoiturage = $avis->getCovoiturage();

        if (!$covoiturage) {
            throw $this->createNotFoundException("Le covoiturage n'existe pas.");
        }
        // Récupération des informations liées
        $conducteur = $avis->getConducteur();//conducteur lié a l'avis;
        $passager = $avis->getPassager();//passager lié a l'avis;
        $rateUser =round($em->getRepository(Avis::class)->rateUser($conducteur),1);
        $commentsUser = $em->getRepository(Avis::class)->findBy(['conducteur' => $conducteur]);

        return $this->render('avis/detail.html.twig', [
            'covoiturage'=>$covoiturage,
            'conducteur'=>$conducteur,
            'commentaires'=>$commentsUser,
            'passager'=>$passager,
            'rateUser'=>$rateUser,

        ]);
}
}

