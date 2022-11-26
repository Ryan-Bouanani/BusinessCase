<?php

namespace App\Form;

use App\Entity\Brand;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotNull;

class BrandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Brand|null $brand */
        $brand = $options['data'] ?? null;
        $isEdit = $brand && $brand->getId();

        $builder
            ->add('label', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Entrer une nom'
                ]
            ])
        ;
        $imageConstraints = [
            // new Image([
            //     'maxSize' => '2018k'
            // ]),
            new File (
                maxSize: '2048k',
                mimeTypes: ['image/png', 'image/jpeg'],
                mimeTypesMessage: 'Ce format d\'image n\'est pas pris en compte',
            )
        ];
        if (!$isEdit || !$brand->getPathImage()) {
            $imageConstraints[] = new NotNull([
                'message' => 'Merci d\'entrer une image',
            ]);
        }
        $builder
            ->add('pathImage', FileType::class, [
                'label' => 'Ajouter un logo',
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
            'data_class' => Brand::class,
        ]);
    }
}
