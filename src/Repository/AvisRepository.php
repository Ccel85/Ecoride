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
        ->andWhere('c.conducteur = :user')
        ->setParameter('user', $user)
        ->orderBy('c.createdAt', 'DESC') // 🔹 Trie par date décroissante (plus récent en premier)
        ->getQuery()
        ->getResult();
}
    //Récuperer note conducteur et faire la moyenne
    public function rateUser(Utilisateur $conducteur): ?float
    {
        return $this->createQueryBuilder('a')
        ->select('AVG(a.rateComments) as avgRate') // Moyenne arrondie à 1 décimale
        ->where('a.conducteur = :conducteur')
        ->andWhere('a.isValid = true') // Ne prend que les avis valides
        ->setParameter('conducteur', $conducteur)
        ->getQuery()
        ->getSingleScalarResult();
    }
    //Afficher les commentaires non validés
    public function invalidComments(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.isValid = :valid')
            ->andwhere('a.isSignal = :signal')
            ->setParameter('valid', false)
            ->setParameter('signal', false)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    //Afficher les commentaires validés
    public function validComments(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.isValid = :valid')
            ->andwhere('a.isSignal = :signal')
            ->setParameter('valid', true)
            ->setParameter('signal', false)
            ->orderBy('a.id', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    //Afficher les commentaires signalés
    public function signalComments(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.isSignal = :signal')
            ->andwhere('a.isValid = :valid')
            ->setParameter('signal', true)
            ->setParameter('valid', false)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
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
