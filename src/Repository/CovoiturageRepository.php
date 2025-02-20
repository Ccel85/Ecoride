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

    public function findCovoiturageByDateOrdered()
    {
        return $this->createQueryBuilder('c')
        ->orderBy('c.dateDepart', 'ASC')  // 🔹 'ASC' pour ordre croissant, 'DESC' pour décroissant
        ->getQuery()
        ->getResult();
}

public function findCovoiturageByDateNear($dateDepart,$lieuDepart,$lieuArrivee)
    {
        return $this->createQueryBuilder('c')
        ->andWhere('c.dateDepart > :dateDepart')
        ->andWhere('c.lieuDepart = :lieuDepart')
        ->andWhere('c.lieuArrivee = :lieuArrivee')
        ->setParameter('dateDepart', $dateDepart)
        ->setParameter('lieuDepart', $lieuDepart)
        ->setParameter('lieuArrivee', $lieuArrivee)
        ->orderBy('c.dateDepart', 'ASC')  // 🔹 'ASC' pour ordre croissant, 'DESC' pour décroissant
        ->getQuery()
        ->getResult();
}

public function covoiturageDuree($covoiturage)
    {
        return $dureeVoyage = $covoiturage->getHeureArrivee() - $covoiturage->getHeureDepart();

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
        ->orderBy('c.dateDepart', 'ASC')
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

    public function nombreCovoiturages()
    {
    return $this->createQueryBuilder('c')
        ->select('COUNT(c.id)')
        ->getQuery()
        ->getSingleScalarResult();
    }

    public function nombreCovoituragesParJour($year, $month)
    {
        $start = new \DateTime("first day of $year-$month");
        $end = new \DateTime("last day of $year-$month");

        return $this->createQueryBuilder('c')
            ->select('c.dateDepart AS jour, COUNT(c.id) AS total')
            ->where('c.dateDepart BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->groupBy('jour')
            ->orderBy('jour', 'ASC')
            ->getQuery()
            ->getArrayResult();

    }

public function nombreCovoituragesDuMois($year,$month)
{

$start = new \DateTime("first day of $year-$month");
$end = new \DateTime("last day of $year-$month");

    return $this->createQueryBuilder('c')
        ->select('COUNT(c.id) as total')
        ->where('c.dateDepart BETWEEN :start AND :end')
        ->setParameter('start', $start)
        ->setParameter('end', $end)
        ->getQuery()
        ->getSingleScalarResult();
}


    public function creditParJour()
    {
        return $this->createQueryBuilder('c')
        ->select("COUNT(c.id) as total , c.dateDepart as jour")
        ->groupBy('jour')
        ->orderBy('jour', 'ASC')
        ->getQuery()
        ->getArrayResult();
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
