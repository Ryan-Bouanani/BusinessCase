<?php

namespace App\Form\Filter;

use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\DateRangeFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\NumberFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\TextFilterType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PromotionFilterType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', NumberFilterType::class, [
                'condition_operator' => FilterOperands::OPERATOR_EQUAL ,
                'attr' => [
                    'class' => 'filterSearch',
                    'placeholder' => 'Id d\'une promotion'
                ]
            ])
            ->add('name', TextFilterType::class, [
                'condition_pattern' => FilterOperands::STRING_CONTAINS,
                'attr' => [
                    'class' => 'filterSearch',
                    'placeholder' => 'Nom d\'une promotion'
                ]
            ])
            ->add('percentage', NumberFilterType::class, [
                'condition_operator' => FilterOperands::OPERATOR_EQUAL ,
                'attr' => [
                    'class' => 'filterSearch',
                    'placeholder' => 'Pourcentage d\'une promotion'
                ]
            ])
            ->add('expirationDate', DateRangeFilterType::class, [
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
}