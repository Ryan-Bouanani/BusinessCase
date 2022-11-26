<?php

namespace App\Form\Filter;

use App\Entity\Brand;
use Doctrine\ORM\EntityRepository;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\EntityFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\NumberRangeFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\TextFilterType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FrontProductFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        // foreach ($options['data'] as $key => $product ) {
                // $prix = ($product[0]->getPriceExclVat());
            // ($this->priceTaxInclService->calcPriceTaxIncl($product->getPriceExclVat(), $product->getTva(), $product->getPromotion()->getPercentage())) * $product['quantity'];
        // }
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
            
            ->add('brand', EntityFilterType::class, [
                'class' => Brand::class,
                'choice_label' => 'label',
                'label' => 'Marque',
                'placeholder' => 'Sélectionner une marque',
                'attr' => [
                    'class' => 'filterSearchSelect', 
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('brand')
                        ->orderBy('brand.label', 'ASC')
                    ;
                },
            ])
        ;
    }
}
