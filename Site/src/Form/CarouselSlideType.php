<?php

namespace App\Form;

use App\Entity\CarouselSlide;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class CarouselSlideType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('imageFile', FileType::class, [
                'label' => 'Image',
                'mapped' => false,
                'required' => false,
                'constraints' => array_filter([
                    $options['require_image'] ? new NotBlank(message: 'Merci de choisir une image.') : null,
                    new File(
                        maxSize: '15M',
                        mimeTypes: ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
                        mimeTypesMessage: 'Merci de choisir une image valide (jpeg, png, webp, gif).',
                    ),
                ]),
            ])
            ->add('caption', TextareaType::class, [
                'label' => 'Texte affiché sur l\'image',
                'required' => false,
            ])
            ->add('altText', TextType::class, [
                'label' => 'Description de l\'image (accessibilité)',
                'required' => false,
            ])
            ->add('sortOrder', IntegerType::class, [
                'label' => 'Ordre d\'affichage',
            ])
            ->add('visible', CheckboxType::class, [
                'label' => 'Visible sur le site public',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CarouselSlide::class,
            'require_image' => false,
        ]);
    }
}
