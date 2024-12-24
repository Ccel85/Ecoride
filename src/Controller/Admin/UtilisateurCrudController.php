<?php

namespace App\Controller\Admin;

use App\Entity\Utilisateur;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class UtilisateurCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Utilisateur::class;
    }
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb= parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $qb->from(Utilisateur::class, 'u');
        $qb->andWhere('u.roles LIKE :role') // Utilisation de MEMBER OF si roles est un tableau
            ->setParameter('role', 'ROLE_EMPLOYE');
        return $qb;
    
    }
    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('nom'),
            TextField::new('prenom'),
            TextField::new('email'),
            IntegerField::new('rate'),
            IntegerField::new('credits'),
            BooleanField::new('isactif'),
        ];
    }
    
}
