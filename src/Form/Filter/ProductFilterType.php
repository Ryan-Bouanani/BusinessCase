<?php

namespace App\Form\Filter;

use App\Entity\Category;
use App\Entity\Image;
use App\Entity\Product;
use Doctrine\ORM\EntityRepository;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\BooleanFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\CheckboxFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\ChoiceFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\DateRangeFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\EntityFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\NumberFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\TextFilterType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Factory\Cache\ChoiceFilter;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductFilterType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextFilterType::class, [
                'condition_pattern' => FilterOperands::STRING_CONTAINS,
                'attr' => [
                    'class' => 'filterSearch',
                    'placeholder' => 'Nom d\'un produit'
                ]
            ])
            ->add('description', TextFilterType::class, [
                'condition_pattern' => FilterOperands::STRING_CONTAINS,
                'attr' => [
                    'class' => 'filterSearch',
                    'placeholder' => 'Description d\'un produit'
                ]
            ])
            ->add('priceExclVat', NumberFilterType::class, [
                'condition_operator' => FilterOperands::OPERATOR_EQUAL,
                'attr' => [
                    'class' => 'filterSearch',
                    'placeholder' => 'Prix d\'un produit'
                ]
            ])
            ->add('active', BooleanFilterType::class, [
                // 'condition_operator' => FilterOperands::OPERATOR_EQUAL,
                'placeholder' => 'Actif',
                'attr' => [
                    'class' => 'filterSearchSelect'
                ]
                
            ])
            ->add('dateAdded', DateRangeFilterType::class, [
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
            ->add('category', EntityFilterType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'placeholder' => 'Categorie',
                'attr' => [
                    'class' => 'filterSearchSelect'
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('category')
                    ->orderBy('category.name', 'ASC')
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