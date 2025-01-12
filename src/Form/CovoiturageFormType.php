<?php

namespace App\Form;

use App\Entity\Voiture;
use App\Entity\Covoiturage;
use App\Repository\VoitureRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class CovoiturageFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user']; // Récupérer l'utilisateur passé dans les options

        $builder
            ->add('prix', IntegerType::class)
            ->add('dateDepart', DateType::class,  [
                'widget' => 'single_text'
            ])
            ->add('lieuDepart', TextType::class)
            ->add('heureDepart',TimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('lieuArrivee', TextType::class)
            ->add('heureArrivee', TimeType::class, [
                'widget' => 'single_text'
            ])
            
            ->add('placeDispo',IntegerType::class)
            /*->add('createdAt', null, [
                'widget' => 'single_text'
            ])*/
            /*->add('utilisateurs', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])*/
            ->add('voiture', EntityType::class, [
                'class' => Voiture::class,
                'choices' => $options['voitures'], // Passe les voitures via les options
                'choice_label' => function (Voiture $voiture) {
                    return $voiture->getConstructeur() . ' - ' . $voiture->getModele();
                },
                'placeholder' => 'Sélectionnez un véhicule',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Covoiturage::class,
            'user' => null,
            'voitures' => [], // Ajoute une option personnalisée
        ]);
    }
}
