<?php

namespace App\Form;

use App\Entity\Customer;
use App\Entity\Gender;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Nom d\'utilisateur',
                'attr' => [                    
                    'placeholder' => 'exemple42',
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse mail',
                'attr' => [
                    'placeholder' => 'example@gmail.com',
                ],
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Prenom',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('dateOfBirth', DateType::class, [
                'label' => 'Date de naissance',
                'widget' => 'single_text',
                'attr' => [
                    'min' => date('1900-01-01'),
                    'max' => date('Y-m-d'),
                ]
            ])
            ->add('gender', EntityType::class, [
                'label' => 'Genre',
                'class' => Gender::class,
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('g')
                        ->orderBy('g.name', 'DESC')
                        ;
                }
            ])
            ->add('RGPDConsent', CheckboxType::class, [
                'mapped' => false,
                'label' => 'En m\'inscrivant à ce site j\'accepte les conditions générales',
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'label' => 'Mot de passe',
                'attr' => [
                    'autocomplete' => 'new-password',
                    'placeholder' => '**************',
                ],
                'constraints' => [
                    new Regex('/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{14,}$/', 'Votre mot de passe doit contenir au minimum 14 caractères avec une 1 majuscule, 1 minuscule, 1 chiffre et 1 caractère spécial '),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Customer::class,
        ]);
    }
}
