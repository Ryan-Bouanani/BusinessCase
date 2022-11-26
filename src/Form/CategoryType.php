<?php

namespace App\Form;

use App\Entity\Category;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la catégorie',
                'attr' => [
                    'placeholder' => 'Entrer un nom'
                ],
            ])
            ->add('categoryParent', EntityType::class, [
                'required' => false,
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Categorie parent',
                'placeholder' => 'Sélectionner une catégorie parent',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('category')
                        ->where('category.categoryParent IS NULL')
                        ->orderBy('category.name', 'ASC')
                    ;
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
}
