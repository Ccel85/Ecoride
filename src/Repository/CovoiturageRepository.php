<?php

namespace App\Repository;

use MongoDB\BSON\UTCDateTime;
use MongoDB\BSON\Regex;
use App\Document\CovoiturageMongo;
use Doctrine\ODM\MongoDB\DocumentManager;

class CovoiturageRepository 
{
    private DocumentManager $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }
    
    /* public function findCovoiturageByDateOrdered()
    {
        $qb = $this->dm->createQueryBuilder(CovoiturageMongo::class);

        $qb->field('dateDepart');
        $qb->sort('dateDepart', 1);

        return $qb->getQuery()->execute();
} */

public function findCovoiturageByDateNear(\DateTimeInterface $dateDepart,?string $lieuDepart,?string $lieuArrivee,?string $prix)
{
        $qb= $this->dm->createQueryBuilder(CovoiturageMongo::class);

        $qb->field('dateDepart') ->gte($dateDepart);

        if ($lieuDepart){
            $qb->field('lieuDepart')->equals(new Regex($lieuDepart, 'i'));
        }
        if ($lieuArrivee) {
            $qb->field('lieuArrivee')->equals(new Regex($lieuArrivee, 'i'));
        }
        if ($prix){
            $qb->field('prix')->lte($prix);
        }
        $qb->sort('dateDepart', 1);
        $qb->limit(10); // Limite pour ne pas surcharger l'affichage

        return iterator_to_array($qb->getQuery()->execute(), false);
}

public function findCovoiturage(?\DateTimeInterface $dateDepart, ?string $lieuDepart, ?string $lieuArrivee, ?string $prix)
{
    $qb = $this->dm->createQueryBuilder(CovoiturageMongo::class);
    // Construction la requête pour filtrer les covoiturages
    if ($dateDepart) {
        $startDate = $dateDepart;
        $endDate = (clone $startDate)->modify('+1 day');
    
        $qb->field('dateDepart')->gte(new UTCDateTime($startDate))->lt(new UTCDateTime($endDate));
    }
    if ($lieuDepart){
        $qb->field('lieuDepart')->equals(new Regex($lieuDepart, 'i'));
    }
    if ($lieuArrivee) {
        $qb->field('lieuArrivee')->equals(new Regex($lieuArrivee, 'i'));
    }
    if ($prix){
        $qb->field('prix')->lte((float)$prix);
    }

    $qb->sort('dateDepart', 1);
    //dd($qb->getQuery()->debug());

    return iterator_to_array($qb->getQuery()->execute(), false);

}

public function covoiturageDuree($covoiturage)
    {
        return $dureeVoyage = $covoiturage->getHeureArrivee() - $covoiturage->getHeureDepart();

}

    /**
    * @return CovoiturageMongo[] Returns an array of Covoiturage objects
    */
    public function findBySearch($date,$depart,$arrivee,$placeDispo): array
    {
    return $this->createQueryBuilder('c')
        ->andWhere('c.date = :date')
        ->andWhere('c.depart LIKE :depart')
        ->andWhere('c.arrivee LIKE :arrivee')
        ->andWhere('c.placeDispo > :placeDispo')
        ->setParameter('date', $date)
        ->setParameter('depart','%' . $depart . '%')
        ->setParameter('arrivee','%' . $arrivee . '%')
        ->setParameter('placeDispo', $placeDispo)
        ->orderBy('c.dateDepart', 'ASC')
        ->getQuery()
        ->getResult();
        }

        //RECHERCHER VEHICULE UTILISE LORS DU COVOITURAGE
    public function rechercheVoiture($utilisateur)
    {
        return $this->createQueryBuilder('v')
        ->where('v.utilisateur = :utilisateur')
        ->setParameter('utilisateur', $utilisateur)
        ->getQuery()
        ->getResult();
    }
    //CALCUL NOMBRE DE COVOITURAGE
    public function nombreCovoiturages()
    {
    return $this->createQueryBuilder('c')
        ->select('COUNT(c.id)')
        ->getQuery()
        ->getSingleScalarResult();
    }
//CALCUL NOMBRE DE COVOITURAGE PAR JOUR
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
//CALCUL NOMBRE DE COVOITURAGE PAR MOIS
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

    //CREDIT PAR JOUR
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
