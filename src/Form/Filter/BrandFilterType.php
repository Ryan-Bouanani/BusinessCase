<?php

namespace App\Form\Filter;

use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\NumberFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\TextFilterType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class BrandFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {     
        $builder
            ->add('id', NumberFilterType::class, [
                'condition_operator' => FilterOperands::OPERATOR_EQUAL,
                'attr' => [
                    'class' => 'filterSearch',
                    'placeholder' => 'Id d\'une marque'
                ]
            ])
            ->add('label', TextFilterType::class, [
                'condition_pattern' => FilterOperands::STRING_CONTAINS,
                'attr' => [
                    'class' => 'filterSearch',
                    'placeholder' => 'Nom d\'une marque'
                ]
            ])
        ;
    }
}