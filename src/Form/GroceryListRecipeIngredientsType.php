<?php

namespace App\Form;

use App\Entity\Ingredient;
use App\Entity\GroceryList;
use App\Entity\Recipe;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class GroceryListRecipeIngredientsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('groceryList', ChoiceType::class, [
                'label' => 'Your Lists',
                'choices' => $options['data']['choices'],
                'placeholder' => 'Select grocery list',
                'required' => true,
                'data' => $options['data']['currentGrocerylistId'],
                'by_reference' => false,
            ])
            ->add('ingredients', EntityType::class, [
                'label' => 'Ingredients',
                'class' => Ingredient::class,
                'choices' => $options['data']['recipe']->getIngredients(),
                'choice_label' => 'title',
                'multiple' => true,
                'expanded' => true,
                'data' => $options['data']['ingredients'],
                'by_reference' => false
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Add recipe with ingredients to list'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
