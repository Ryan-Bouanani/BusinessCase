<?php

namespace App\Form;

use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\Product;
use App\Entity\Promotion;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Product|null $product */
        $product = $options['data'] ?? null;
        $isEdit = $product && $product->getId();

        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Entrer un nom'
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'placeholder' => 'Entrer une description'
                ],
            ])
            ->add('priceExclVat', NumberType::class, [
                'label' => 'Prix (en €)',
                'attr' => [
                    'placeholder' => 'Entrer un prix hors taxe',
                    'min' => 0
                ],
            ])
            ->add('tva', NumberType::class, [
                'label' => 'TVA (en %)',
                'attr' => [
                    'placeholder' => 'Entrer une tva',
                    'min' => 0
                ],
            ])
            ->add('active', CheckboxType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input',
                ],
                'label' => 'Actif',
                'label_attr' => [
                    'class' => 'form-check-label'
                ],
            ])
            ->add('brand', EntityType::class, [
                'class' => Brand::class,
                'choice_label' => 'label',
                'label' => 'Marque',
                'placeholder' => 'Selectionner une marque',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('brand')
                        ->orderBy('brand.label', 'ASC')
                    ;
                }
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Categorie',
                'placeholder' => 'Selectionner une categorie',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('category')
                        ->where('category.categoryParent IS NOT NULL')
                        ->orderBy('category.name', 'ASC')
                    ;
                }
                ])
            ->add('promotion', EntityType::class, [
                'class' => Promotion::class,
                'required' => false,
                'placeholder' => 'Selectionner une promotion',
                'choice_label' => 'name',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('promotion')
                        ->orderBy('promotion.name', 'ASC')
                    ;
                }
            ])
        ;

  
        $imageConstraints = [];

        if (!$isEdit || !$product->getImages()) {
            $imageConstraints[] = new NotBlank([
                'message' => 'Merci d\'entrer une image',
            ]);
        }

        $builder
            ->add('images', FileType::class, [
                'help' => 'La première image choisie sera l\'image principale',
                'label' => 'Ajouter une image',
                'multiple' => true,
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control mb-2 mt-2'
                ],
                'constraints' => $imageConstraints,
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}

