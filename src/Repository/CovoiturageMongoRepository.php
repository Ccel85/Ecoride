<?php

namespace App\Repository;

use MongoDB\BSON\UTCDateTime;
use MongoDB\BSON\Regex;
use App\Document\CovoiturageMongo;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

class CovoiturageMongoRepository extends DocumentRepository
{
    
    /* public function findCovoiturageByDateOrdered()
    {
        $qb = $this->dm->createQueryBuilder(CovoiturageMongo::class);

        $qb->field('dateDepart');
        $qb->sort('dateDepart', 1);

        return $qb->getQuery()->execute();
} */
//Affichage covoiturage à date supérieure
public function findCovoiturageByDateNear(
    \DateTimeInterface $dateDepart,
    ?string $lieuDepart,
    ?string $lieuArrivee,
    ?string $prix)
    {
        $qb = $this->createQueryBuilder();

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
// Recherche Covoiturage
public function findCovoiturage(
    ?\DateTimeInterface $dateDepart,
    ?string $lieuDepart,
    ?string $lieuArrivee,
    ?string $prix)
    {
        $qb = $this->createQueryBuilder();

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
//Retourne la durée du covoiturage
public function covoiturageDuree($covoiturage)
    {
        return $dureeVoyage = $covoiturage->getHeureArrivee() - $covoiturage->getHeureDepart();

    }

    //Retourne une recherche de covoiturage
    /**
    * @return CovoiturageMongo[] Returns an array of Covoiturage objects
    */
    /* public function findBySearch($date,$depart,$arrivee,$placeDispo): array */
      /*   {
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
            } */
    //CALCUL NOMBRE DE COVOITURAGE
    public function nombreCovoituragesMois(int $year, int $month): int
{
    $month = sprintf('%02d', $month);
    
    $start = new \DateTimeImmutable("$year-$month-01");
    $end = $start->modify('last day of this month')->setTime(23, 59, 59);

    return $this->dm->createQueryBuilder(CovoiturageMongo::class)
        ->field('dateDepart')->gte($start)->lte($end)
        ->count()
        ->getQuery()
        ->execute();
}
    //CALCUL NOMBRE DE COVOITURAGE PAR JOUR DANS UN MOIS DONNE
    public function nombreCovoiturages($year, $month)
        {
        $month = sprintf('%02d', $month);
        $start = new \DateTimeImmutable("$year-$month-01");
        $end = $start->modify('last day of this month')->setTime(23, 59, 59);

        $aggregationBuilder = $this->dm->createAggregationBuilder(CovoiturageMongo::class);

        $result = $aggregationBuilder
            ->match()
                ->field('dateDepart')->gte($start)->lte($end)
            ->group()
                ->field('_id')->expression([
                    'year' => ['$year' => '$dateDepart'],
                    'month' => ['$month' => '$dateDepart'],
                    'day' => ['$dayOfMonth' => '$dateDepart']
                ])
                ->field('total')->sum(1)
            ->sort(['_id.year' => 1, '_id.month' => 1, '_id.day' => 1])
            ->execute()
            ->toArray();
            return $result;
    }

        public function findByGo(bool $go): int
    {
        return $this->dm->createQueryBuilder(CovoiturageMongo::class)
            ->field('isGo')->equals($go)
            ->count()
            ->getQuery()
            ->execute();
    }
}
    //CREDIT PAR JOUR
    /*   public function creditParJour()
        {
            return $this->createQueryBuilder('c')
            ->select("COUNT(c.id) as total , c.dateDepart as jour")
            ->groupBy('jour')
            ->orderBy('jour', 'ASC')
            ->getQuery()
            ->getArrayResult();
        } */


