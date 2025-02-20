<?php

namespace App\Controller\Admin;

use DateTime;
use DatePeriod;
use DateInterval;
use App\Entity\Avis;
use App\Entity\Covoiturage;
use Symfony\UX\Chartjs\Model\Chart;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard/admin', name: 'app_admin_dashboard')]
    public function admin(EntityManagerinterface $em,ChartBuilderInterface $chartBuilder,Request $request): Response
    {
        //test
    $covoiturages = $em->getRepository(Covoiturage::class);
    $countCovoiturages = $covoiturages->nombreCovoiturages();
    $creditscovoiturage = $countCovoiturages * 2;
    $month = $request->query->get('month');
    $year = 2025;
   /*  $yearEnd = new \Datetime('Y');
    $yearStart = $yearEnd->modify('-2 year') ;
    $interval = new \DateInterval('P1Y');
    $yearPeriod = new \DatePeriod($yearStart,$interval,$yearEnd->modify('+1 year')); */
    
        // nombre de covoiturage par mois
        $totalMois = $month ? $em->getRepository(Covoiturage::class)->nombreCovoituragesDuMois($year, $month) : 0;
        // nombre de covoiturage par jour
        $resultData = $covoiturages->nombreCovoituragesParJour($year, $month) ?: [];


    $result = [];
    foreach ($resultData as $row) {
        $dateKey = $row['jour']->format('d-m-Y');
        $result[$dateKey] = $row;
    }

    // date
    $start = new \DateTime("first day of $year-$month");
    $end = new \DateTime("last day of $year-$month");
    $interval = new \DateInterval('P1D');
    $period = new \DatePeriod($start, $interval, $end->modify('+1 day'));
    //mise en place des datas pour les charts
    $data = ['labels' => [],
            'values' => [],
            'credits' => []
        ];
    foreach ($period as $date) {
        $formattedDate = $date->format('d-m-Y');
        $data['labels'][] = $formattedDate;
        $data['values'][] = isset($result[$formattedDate]) ? $result[$formattedDate]['total'] : 0;
        $data['credits'][] = isset($result[$formattedDate]) ? $result[$formattedDate]['total'] * 2 : 0;
    }
        //création du graphique
        $chart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $chart->setData([
            'labels' =>$data['labels'],
            'datasets' => [
                [
                    'label' => 'Nombre de covoiturage par jour',
                    'subtitle'=>[           // sous titre graphisme
                        'display'=> true,
                        'text' =>  $month,
                    ],  // fin du sous titre
                    'backgroundColor' => ' #89DF98',
                    'borderColor' =>' #39B54E',
                    'data' => $data['values'],
                ],
            ],
        ]);
        $maxValue = !empty($data['values']) ? max($data['values']) : 0;
        $chart->setOptions([
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' =>$maxValue + 1,
                    'ticks'=> [
                        'stepSize'=> 1] // force l'echelle à une unitée
                ],
            ],
            'plugins' => [
                'legend' => [
                    'labels' => [
                        'font' => [
                            'size' => 20
                        ]
                    ]
                ]
            ],
        ]);

        //création du graphique
        $chartCredit = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chartCredit->setData([
            'labels' =>$data['labels'],
            'datasets' => [
                [
                    'label' => 'Nombre de crédit par jour',
                    'backgroundColor' => ' #89DF98',
                    'borderColor' =>' #39B54E',
                    'data' =>$data['credits'],
                ],
            ],
        ]);

        $maxValue = !empty($data['credits']) ? max($data['credits']) : 0;
        $chartCredit->setOptions([
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' =>$maxValue + 1,
                    'ticks'=> [
                        'stepSize'=> 1] // force l'echelle à une unitée
                ],
            ],
            'plugins' => [
                'legend' => [
                    'labels' => [
                        'font' => [
                            'size' => 20
                        ]
                    ]
                ]
            ],
        ]);


        return $this->render('admin/dashboard/admin.html.twig',[
            'creditscovoiturage' => $creditscovoiturage,
            'chart'=>$chart,
            'chartCredit'=>$chartCredit,
            /* 'credit'=>$credits, */
            'totalMois'=>$totalMois,
            'month'=>$month,
            'year'=>$year,
            /* 'yearPeriod'=>$yearPeriod, */
            
        ]);
    }

    #[Route('/admin/dashboard/employe', name: 'app_employe_dashboard')]
    public function employe(EntityManagerInterface $em): Response
    {
        $invalidComments = $em->getRepository(Avis::class)->invalidComments();
        $countInvalidComments = count($invalidComments);
        $signalComments = $em->getRepository(Avis::class)->signalComments();
        $countSignalComments = count($signalComments);
        
        return $this->render('admin/dashboard/employe.html.twig',[
            'invalidComments'=>$invalidComments,
            'countInvalidComments'=>$countInvalidComments,
            'signalComments'=> $signalComments,
            'countSignalComments'=>$countSignalComments,
        ]);
    }
}
