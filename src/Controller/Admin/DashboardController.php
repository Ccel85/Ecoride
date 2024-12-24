<?php

namespace App\Controller\Admin;

use App\Entity\Utilisateur;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Controller\Admin\UtilisateurCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // Option : rediriger vers un CRUD spécifique (exemple : UtilisateurCrudController)
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        
        return $this->redirect(
            $adminUrlGenerator->setController(UtilisateurCrudController::class)->generateUrl()
        );
    }


        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Ecoride');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::section('Gestion des Utilisateurs');
        yield MenuItem::linkToCrud('Liste des Utilisateurs', 'fas fa-users', Utilisateur::class);
        yield MenuItem::linkToCrud('Liste des employés', 'fas fa-users', Utilisateur::class);
        // Section supplémentaire
        yield MenuItem::section('Autres');
        yield MenuItem::linkToRoute('Page d\'accueil', 'fas fa-home', 'app_home');
        
        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
    }
}
