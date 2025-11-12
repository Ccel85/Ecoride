<?php

namespace App\Form;

use App\Entity\Voiture;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class VoitureFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('immat',TextType::class,[
                'required' => true,
                'label' => 'Immatriculation',
                'attr' => [
                    'pattern' => '^[0-9]{2}-[A-Z]{3}-[0-9]{2}$',
                    'title' => 'Format attendu : 12-ABC-34',
                    'placeholder' => '12-ABC-34',
                    'class' => 'form-control'
    ],
            ])
            ->add('firstImmat',DateType::class, [
                'label' => '1ère immatriculation',
                'format' => 'yyyy-MM-dd',
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('constructeur',TextType::class,[
                'required' => true,
                'label' => 'constructeur',
            ])
            ->add('modele',TextType::class,[
                'label' => 'modèle',
                'required' => true,
            ])
            ->add('couleur',TextType::class,[
                'label' => 'couleur',
                'required' => true,
            ])
            ->add('nbrePlace',IntegerType::class,[
                'label' => 'nbrePlace',
            ])
            ->add('energie',ChoiceType::class,[
                'choices' => [
                    'Diesel' => 'Diesel',
                    'Hybride' => 'Hybride',
                    'Essence' => 'Essence',
                    'Electrique' => 'Electrique'],
                    'label' => 'Énergie',
            ])
            ->add('options',TextareaType::class,[
                'label' => 'options',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Voiture::class,
            'utilisateur' => null,
            'voitures' => [],
            'csrf_protection'=> true,
            'csrf_field_name'=>'_token',
            'csrf_token_id'=>'Voiture',
        ]);
    }
}