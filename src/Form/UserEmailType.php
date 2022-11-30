<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;

class UserEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', RepeatedType::class, [
                'type' => EmailType::class,
                'invalid_message' => 'Les adresses email ne correspondent pas.',
                'required' => true,
                'first_options' => [
                    'attr' => [
                        'placeholder' => 'exemple@exemple.fr',
                    ],
                    'label' => 'Adresse mail'
                ],
                'second_options' => [
                    'label' => 'Confirmation de l\'adresse mail'
                ],
                'attr' => [
                    'placeholder' => 'exemple@exemple.fr',
                    'autocomplete' => 'off'
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
            ])
        ;
    }
}
