<?php

namespace App\Form\Filter;

use App\Entity\Category;
use Doctrine\ORM\EntityRepository;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\EntityFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\NumberRangeFilterType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class FrontBrandFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('priceExclVat' , NumberRangeFilterType::class, [
                'attr' =>  [
                    'class' => 'filterRangeInput'
                ],
                'left_number_options' => [
                    'label' => 'Entre',
                    'condition_operator' => FilterOperands::OPERATOR_GREATER_THAN_EQUAL ,
                    'attr' => [
                        'class' => 'filterSearch', 
                    ],
                ],
                'right_number_options' => [
                    'condition_operator' => FilterOperands::OPERATOR_LOWER_THAN_EQUAL ,
                    'label' => 'et',
                    'attr' => [
                        'class' => 'filterSearch', 
                    ],
                ]
            ])          
            ->add('category', EntityFilterType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Marque',
                'placeholder' => 'Sélectionner une catégorie',
                'attr' => [
                    'class' => 'filterSearchSelect', 
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('category')
                    ->orderBy('category.name', 'ASC')
                    ->where('category.categoryParent IS NOT NULL')
                    ;
                },
            ])
        ;
    }
}
