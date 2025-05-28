<?php

namespace App\Repository;

use MongoDB\BSON\UTCDateTime;
use MongoDB\BSON\Regex;
use App\Document\CovoiturageMongo;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

class CovoiturageMongoRepository extends DocumentRepository
{

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
        /* $qb->limit(10); // Limite pour ne pas surcharger l'affichage */

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

//Calcul le nombre de covoiturage
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
//Calcul le nombre de covoiturage par mois
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

// Rechercher covoiturage démarré
public function findByGo(bool $go): int
    {
        return $this->dm->createQueryBuilder(CovoiturageMongo::class)
            ->field('isGo')->equals($go)
            ->count()
            ->getQuery()
            ->execute();
    }
}


