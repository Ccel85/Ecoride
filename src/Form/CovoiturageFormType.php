<?php

namespace App\Form;

use App\Entity\Voiture;
use App\Document\CovoiturageMongo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class CovoiturageFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $utilisateur = $options['utilisateur']; // Récupérer l'utilisateur passé dans les options
        $voitures = $options['voitures'];

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
            
            ->add('voitureId', ChoiceType::class, [
                'label' => 'Sélectionnez votre véhicule',
                'choices' => $voitures, // Liste des voitures du conducteur
                'choice_label' => function (Voiture $voiture) {
                    return $voiture->getConstructeur() . ' - ' . $voiture->getModele(); // Affichage
                },
                'choice_value' => function (?Voiture $voiture) {
                    return $voiture ? (string) $voiture->getId() : ''; // 🔥 Convertit l'ID en string
                },
                'placeholder' => 'Choisissez une voiture',
                'required' => true,
                'mapped' => false 
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CovoiturageMongo::class,
            'utilisateur' => null,
            'voitures' => [], // Ajoute une option personnalisée
            'csrf_protection'=> true,
            'csrf_field_name'=>'_token',
            'csrf_token_id'=>'Covoiturage',
        ]);
    }
}
