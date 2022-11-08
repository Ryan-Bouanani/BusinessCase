<?php

namespace App\Form\Filter;

use App\Entity\Address;
use App\Entity\Customer;
use App\Entity\MeanOfPayment;
use App\Entity\Status;
use Doctrine\ORM\EntityRepository;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\DateRangeFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\EntityFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\NumberFilterType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderFilterType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', NumberFilterType::class, [
                'condition_operator' => FilterOperands::OPERATOR_EQUAL ,
                'attr' => [
                    'class' => 'filterSearch',
                    'placeholder' => 'Id d\'une commande'
                ]
            ])
            ->add('customer', EntityFilterType::class, [
                'class' => Customer::class,
                'choice_label' => 'username',
                'placeholder' => 'Client',
                'attr' => [
                    'class' => 'filterSearchSelect'
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('customer')
                    ->orderBy('customer.username', 'ASC')
                    ;
                }
            ])
            ->add('address', EntityFilterType::class, [
                'class' => Address::class,
                'choice_label' => 'country',
                'placeholder' => 'Adresse',
                'attr' => [
                    'class' => 'filterSearchSelect'
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('address')
                    ->orderBy('address.country', 'ASC')
                    ;
                }
            ])
            ->add('billingDate', DateRangeFilterType::class, [
                'attr' => [
                    'class' => 'filterSearchDate', 
                ],
                'left_date_options' => [
                    'label' => 'De',
                    'widget' => 'single_text',
                    'attr' => [
                        'class' => 'inputFilterDate', 
                    ],
                ],
                'right_date_options' => [
                    'label' => 'à',
                    'widget' => 'single_text',
                    'attr' => [
                        'class' => 'inputFilterDate', 
                    ],
                ]
            ])
            ->add('meanOfPayment', EntityFilterType::class, [
                'class' => MeanOfPayment::class,
                'choice_label' => 'designation',
                'placeholder' => 'Moyen de paiement',
                'attr' => [
                    'class' => 'filterSearchSelect'
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('meanOfPayment')
                    ->orderBy('meanOfPayment.designation', 'ASC')
                    ;
                }
            ])
            ->add('status', EntityFilterType::class, [
                'class' => Status::class,
                'choice_label' => 'name',
                'placeholder' => 'Status',
                'attr' => [
                    'class' => 'filterSearchSelect'
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('status')
                    ->orderBy('status.name', 'ASC')
                    ;
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }

}