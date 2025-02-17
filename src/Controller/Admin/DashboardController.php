<?php

namespace App\Controller\Admin;

use DateTime;
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
        $countCovoiturages = $em->getRepository(Covoiturage::class)->nombreCovoiturages();
        $creditscovoiturage = $countCovoiturages * 2;
        $month = $request->query->get('month');
        
        if($month){
            
            try{
                $year=2025;
                $totalMois = $em->getRepository(Covoiturage::class)->nombreCovoituragesDuMois($year,$month);
        
            } catch (\Exception $e) {
                    $this->addFlash('error', 'Date invalide.');
                    
                }
            } else {

                $totalMois = 0;
            }

        $covoiturages = $em->getRepository(Covoiturage::class);
        $result = $covoiturages->nombreCovoituragesParJour();
        $credits = $covoiturages->creditParJour();
        $data = [
            'labels' => [],
            'values' => [],
            'credits'=>[]
        ];
        
        foreach ($result as $row){
            $date = is_string($row['jour']) ? new \DateTime($row['jour']) : $row['jour'];
            $data['labels'][] = $date->format('d/m/Y');
            $data['values'][] = $row['total'];
            $data['credits'][]=$row['total'] * 2;
        }
        //création du graphique
        $chart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $chart->setData([
            'labels' =>$data['labels'],
            'datasets' => [
                [
                    'label' => 'Nombre de covoiturage par jour',
                    'backgroundColor' => ' #89DF98',
                    'borderColor' =>' #39B54E',
                    'data' =>$data['values'],
                ],
            ],
        ]);
    
        $chart->setOptions([
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' =>'total',
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
    
        $chartCredit->setOptions([
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' =>'total',
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
            'credit'=>$credits,
            'totalMois'=>$totalMois,
            'month'=>$month,
            
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
