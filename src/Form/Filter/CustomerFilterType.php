<?php

namespace App\Form\Filter;

use App\Entity\Gender;
use Doctrine\ORM\EntityRepository;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\DateRangeFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\EntityFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\NumberFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\TextFilterType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerFilterType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', NumberFilterType::class, [
                'condition_operator' => FilterOperands::OPERATOR_EQUAL ,
                'attr' => [
                    'class' => 'filterSearch',
                    'placeholder' => 'Id d\'un produit'
                ]
            ])
            ->add('email', TextFilterType::class, [
                'condition_pattern' => FilterOperands::STRING_CONTAINS,
                'attr' => [
                    'class' => 'filterSearch',
                    'placeholder' => 'Email'
                ]
            ])
            ->add('username', TextFilterType::class, [
                'condition_pattern' => FilterOperands::STRING_CONTAINS,
                'attr' => [
                    'class' => 'filterSearch',
                    'placeholder' => 'Nom d\'utilisateur'
                ]
            ])
            ->add('firstName', TextFilterType::class, [
                'condition_pattern' => FilterOperands::STRING_CONTAINS,
                'attr' => [
                    'class' => 'filterSearch',
                    'placeholder' => 'Prenom d\'un client'
                ]
            ])
            ->add('lastName', TextFilterType::class, [
                'condition_pattern' => FilterOperands::STRING_CONTAINS,
                'attr' => [
                    'class' => 'filterSearch',
                    'placeholder' => 'Nom d\'un client'
                ]
            ])
            ->add('gender', EntityFilterType::class, [
                'class' => Gender::class,
                'choice_label' => 'name',
                'placeholder' => 'Genre',
                'attr' => [
                    'class' => 'filterSearchSelect'
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('gender')
                    ->orderBy('gender.name', 'ASC')
                    ;
                }
            ])
            ->add('registrationDate', DateRangeFilterType::class, [
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }

}