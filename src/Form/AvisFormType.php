<?php

namespace App\Form;

use App\Entity\Avis;
use App\Entity\Utilisateur;
use Doctrine\DBAL\Types\BooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class AvisFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('comments',TextType::class)
            ->add('rateComments',IntegerType::class)
            ->add('signaler', HiddenType::class, [
                'mapped' => false, // Il sera défini dans le contrôleur
            ]);
            /* ->add('submit', SubmitType::class, ['label' => 'Envoyer'])
            ->add('signaler', SubmitType::class, ['label' => 'Signaler']); */
            
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Avis::class,
        ]);
    }
}
