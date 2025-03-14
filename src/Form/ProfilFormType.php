<?php

namespace App\Form;
use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class ProfilFormType extends AbstractType

{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email',EmailType::class)
            ->add('nom',TextType::class)
            ->add('prenom',TextType::class)
            ->add('pseudo',TextType::class)
            /* ->add('isConducteur',ChoiceType::class,[
                'choices' => [
                    'Conducteur' => 'Conducteur',
                    'Passager' => 'Passager',
                    'Conducteur et passager' => 'Conducteur et passager'],
                'mapped' => false,
                'required' => false,
                    ]) */
            /* ->add('photo_path') */
            ->add('observation',TextType::class,[
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
            'csrf_protection'=> true,
            'csrf_field_name'=>'_token',
            'csrf_token_id'=>'Profil',
        ]);
    }
}
