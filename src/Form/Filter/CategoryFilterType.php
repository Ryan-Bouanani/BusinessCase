<?php

namespace App\Form\Filter;

use App\Entity\Category;
use Doctrine\ORM\EntityRepository;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\EntityFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\NumberFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\TextFilterType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryFilterType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder 
            ->add('id', NumberFilterType::class, [
                'condition_operator' => FilterOperands::OPERATOR_EQUAL,
                'attr' => [
                    'class' => 'filterSearch',
                    'placeholder' => 'Id d\'une catégorie'
                ]
            ])
            ->add('name', TextFilterType::class, [
                'condition_pattern' => FilterOperands::STRING_CONTAINS,
                'attr' => [
                    'class' => 'filterSearch',
                    'placeholder' => 'Nom d\'une catégorie'
                ]
            ])
            ->add('categoryParent', EntityFilterType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'placeholder' => 'Categorie parent',
                'attr' => [
                    'class' => 'filterSearchSelect'
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('category')
                        ->where('category.categoryParent IS NULL')
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