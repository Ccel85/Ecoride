<?php

namespace App\Repository;

use App\Entity\Utilisateur;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @extends ServiceEntityRepository<Utilisateur>
 */
class UtilisateurRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Utilisateur) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    
    /**
     * @return Utilisateur[] Returns an array of Utilisateur objects
     */
    public function findByRole(string $role): array
{
    return $this->createQueryBuilder('u')
        ->where('u.roles IS NOT NULL') // Assurez-vous que le champ n'est pas NULL
        ->andWhere('u.roles LIKE :role')
        ->setParameter('role', '%"' . $role . '"%') // Correspond à un rôle dans un tableau JSON
        ->getQuery()
        ->getResult();
}

    public function majCredit (Security $security,Utilisateur $utilisateur,EntityManager $entityManager): Int
{
    /* $utilisateur = $security->getUser(); // Récupérer l'utilisateur connecté
    

    if (!$utilisateur) {
        throw $this->createAccessDeniedException('L\'utilisateur n\'est pas connecté');
    } */    
    $majCredit = $utilisateur->getCredits()- 2;

    // Mettre à jour l'entité Utilisateur
    $utilisateur->setCredits($majCredit);

    $entityManager->persist($utilisateur);
    $entityManager->flush();

    return $majCredit;
}


}
//    public function findOneBySomeField($value): ?Utilisateur
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

