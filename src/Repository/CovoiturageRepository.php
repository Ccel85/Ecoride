<?php

namespace App\Repository;

use App\Entity\Covoiturage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Covoiturage>
 */
class CovoiturageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Covoiturage::class);
    }

   /**
    * @return Covoiturage[] Returns an array of Covoiturage objects
    */
    public function findBySearch($date,$depart,$arrivee,$placeDispo): array
    {
    return $this->createQueryBuilder('c')
        ->andWhere('c.date = :date')
        ->andWhere('c.depart = :depart')
        ->andWhere('c.arrivee = :arrivee')
        ->andWhere('c.placeDispo = :placeDispo')
        ->setParameter('date', $date)
        ->setParameter('depart', $depart)
        ->setParameter('arrivee', $arrivee)
        ->setParameter('placeDispo', $placeDispo)
        //->orderBy('c.id', 'ASC')
        ->setMaxResults(10)
        ->getQuery()
        ->getResult()
    ;
        }
    public function rechercheVoiture($utilisateur)
    {
        return $this->createQueryBuilder('v')
        ->where('v.utilisateur = :utilisateur')
        ->setParameter('utilisateur', $utilisateur)
        ->getQuery()
        ->getResult();
    }
//    public function findOneBySomeField($value): ?Covoiturage
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
