<?php

namespace App\Form;

use App\Entity\Basket;
use App\Entity\MeanOfPayment;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MeanOfPaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('meanOfPayment', EntityType::class, [
                'class' => MeanOfPayment::class,
                'choice_label' => 'designation',
                // 'mapped' => false,
                'expanded' => true,
                'required' => true,
            ])
        ;
    }
    
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Basket::class,
            
        ]);
    }
}
