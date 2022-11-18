<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Regex;

class ResetPasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password', PasswordType::class, [
                'label' => 'Entrez votre nouveau mot de passe',
                'attr' => [
                    'placeholder' => '******',
                ],
                'constraints' => [
                    // new Assert\Length([
                    //     'min' => 6,
                    //     'max' => 255,
                    //     'minMessage' => 'Veuiller entrer un mot de passe contenant au minimum {{ limit }} caractères',
                    //     'maxMessage' => 'Veuiller entrer un mot de passe contenant au maximum {{ limit }} caractères',
                    // ]),
                    new Regex('/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{14,}$/', 'Votre mot de passe doit contenir au minimum 14 caractères avec une 1 majuscule, 1 minuscule, 1 chiffre et1caractère spécial '),
                ],
                
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
