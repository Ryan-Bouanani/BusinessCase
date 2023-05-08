<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class AddToBasketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('quantity', NumberType::class, [
                'label' => 'Quantity',
                'required' => true,
                'data' => 0,
                'attr' => [
                    'min' => 1,
                    'max' => 10,
                    'step' => 1,
                    'class' => 'quantity',
                ]
            ])
            ->add('increment', ButtonType::class, [
                'label' => '+',
                'attr' => [
                    'class' => 'btn btn-success',
                    'onclick' => 'incrementQuantity()',
                ],
            ])
            ->add('decrement', ButtonType::class, [
                'label' => '-',
                'attr' => [
                    'class' => 'btn btn-danger',
                    'onclick' => 'decrementQuantity()',
                ],
            ]);
    }
}