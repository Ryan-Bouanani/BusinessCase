<?php

namespace App\Form;

use App\Entity\Address;
use App\Entity\Basket;
use App\Entity\Customer;
use App\Entity\MeanOfPayment;
use App\Entity\Status;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class BasketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $contentShoppingCarts = $options['contentShoppingCarts'];

        $builder
            ->add('customer', EntityType::class, [
                'class' => Customer::class,
                'choice_label' => 'username',
                'label' => 'Client',
                'placeholder' => 'Selectionner un client',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('customer')
                    ->orderBy('customer.username', 'ASC')
                    ;
                }
            ])
            ->add('meanOfPayment', EntityType::class, [
                'class' => MeanOfPayment::class,
                'choice_label' => 'designation',
                'label' => 'Moyen de paiement',
                'placeholder' => 'Selectionner un moyen de paiement',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('meanOfPayment')
                    ->orderBy('meanOfPayment.designation', 'ASC')
                    ;
                }
            ])
            ->add('status', EntityType::class, [
                'class' => Status::class,
                'choice_label' => 'name',
                'label' => 'Status',
                'placeholder' => 'Selectionner un status',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('status')
                    ->orderBy('status.name', 'ASC')
                    ;
                },
            ])
            ->add('address', EntityType::class, [
                'class' => Address::class,
                'choice_label' => 'country',
                'label' => 'Pays',
                'placeholder' => 'Selectionner un pays',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('address')
                    ->orderBy('address.country', 'ASC')
                    ;
                },
            ])


            ;
            if ($contentShoppingCarts !== null ) {
                $builder
                ->add('product', TextType::class, [
                    'required' => false,
                    'mapped' => false,
                    'label' => 'Nom produit',
                    'attr' => [
                        'placeholder' => 'Nom d\'un Produit',
                        'class' => 'addProduct',
                    ],
                ])
                ->add('quantityNewProduct', NumberType::class, [
                    'data_class'=> null,
                    'mapped'=> false,
                    'label' => 'Quantité',
                    'data' => 1, 
                    'attr' => [
                        'class' => 'quantity'
                    ],
                ])
                ;
                $count = 1;
                foreach ( $contentShoppingCarts as $line) { 
                    $builder 
                    ->add('quantity'.$count, NumberType::class, [
                        'label' => false,
                        'data_class'=>null,
                        'mapped'=> false,
                        'data' => $line->getQuantity(), 
                         'attr' => [
                            'class' => 'quantity'
                        ],
                    ])
                    ;
                    $count++;
                }
            }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Basket::class,
            'contentShoppingCarts' => null,
        ]);
    }
}
