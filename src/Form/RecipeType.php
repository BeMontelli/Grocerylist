<?php

namespace App\Form;

use App\Entity\Recipe;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecipeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title',TextType::class, [
                'label' => 'Recipe title'
            ])
            ->add('slug',TextType::class, [
                'label' => 'Slug'
            ])
            ->add('price',NumberType::class, [
                'label' => 'Price (€)',
                'attr' => [
                    'min' => 0,
                    'max' => 100,
                    'type' => 'number',
                ]
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Content'
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save Recipe'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recipe::class,
        ]);
    }
}
