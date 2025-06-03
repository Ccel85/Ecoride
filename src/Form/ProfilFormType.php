<?php

namespace App\Form;
use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;
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
            ->add('imageFile', VichImageType::class, [
            'required' => false,
            'allow_delete' => false,
            'download_uri' => false,
            'label' => 'Photo de profil',
            'image_uri' => false,
        ])
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
