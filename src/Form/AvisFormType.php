<?php

namespace App\Form;

use App\Entity\Avis;
use App\Entity\Utilisateur;
use Doctrine\DBAL\Types\BooleanType;
use Doctrine\DBAL\Types\JsonType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class AvisFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('comments',TextareaType::class)
            ->add('rateComments',IntegerType::class)
            ->add('signaler', HiddenType::class, [
                'mapped' => false, // Il sera défini dans le contrôleur
            ]);
           /*  ->add('conducteur',IntegerType::class, [
                'required' => false,
            ])
            ->add('passager',IntegerType::class, [
                'required' => false, 
            ])
            ->add('covoiturage',TextType::class, [
                'required' => false,
            ]); */
            /* ->add('submit', SubmitType::class, ['label' => 'Envoyer'])
            ->add('signaler', SubmitType::class, ['label' => 'Signaler']); */
            
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Avis::class,
            'csrf_protection'=> true,
            'csrf_field_name'=>'_token',
            'csrf_token_id'=>'Avis',
        ]);
    }
}
