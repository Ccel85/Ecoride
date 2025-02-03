<?php

namespace App\Repository;

use App\Entity\Avis;
use App\Entity\Utilisateur;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Avis>
 */
class AvisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Avis::class);
    }

    public function findCommentairesByUserOrdered(Utilisateur $user)
{
    return $this->createQueryBuilder('c')
        ->andWhere('c.utilisateur = :user')
        ->setParameter('user', $user)
        ->orderBy('c.createdAt', 'DESC') // 🔹 Trie par date décroissante (plus récent en premier)
        ->getQuery()
        ->getResult();
}

public function rateUser(Utilisateur $utilisateur): ?float
{
    return $this->createQueryBuilder('a')
    ->select('AVG(a.rateComments) as avgRate') // Moyenne arrondie à 1 décimale
    ->where('a.utilisateur = :utilisateur')
    ->andWhere('a.isValid = true') // Ne prend que les avis valides
    ->setParameter('utilisateur', $utilisateur)
    ->getQuery()
    ->getSingleScalarResult();
}
//    /**
//     * @return Avis[] Returns an array of Avis objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Avis
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
