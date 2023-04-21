<?php

namespace App\Form;

use App\Entity\Address;
use App\Entity\Customer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
         /** @var Customer $customer */
        $customer = $options['customer']?? null;
        /** @var Address|null $address */
        $address = $options['data'] ?? null;
        $haveAddress = $address && $address->getId();

        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'placeholder' => 'Prénom'
                ],
                'data' => $haveAddress ? $address->getFirstName() : $customer->getFirstName(),
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Nom'
                ],
                'data' => $haveAddress ? $address->getLastName() : $customer->getLastName(),
            ])
            ->add('line1', TextType::class, [
                'label' => 'Rue et numero de rue',
                'attr' => [
                    'placeholder' => 'Rue et numero de rue'
                ]
            ])
            ->add('line2', TextType::class, [
                'label' => 'Complément d\'adresse',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Complément d\'adresse'
                ]
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
                'attr' => [
                    'placeholder' => 'Ville'
                ]
            ])
            ->add('country', TextType::class, [
                'label' => 'Pays',
                'attr' => [
                    'placeholder' => 'Pays'
                ]
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Code postal',
                'attr' => [
                    'placeholder' => 'Code postal'
                ]
            ])
            ->add('phoneNumber', TextType::class, [
                'label' => 'Numéro de téléphone',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Numéro de téléphone'
                ],
            ])
        ;
        
        // if ($customer) {
        //     $builder->get('firstName')->setData($customer->getFirstName());
        //     $builder->get('lastName')->setData($options['customer']->getLastName());
        // }
        // dd( $builder->get('firstName')->getData());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
            'customer' => null,
        ]);
    }
}
