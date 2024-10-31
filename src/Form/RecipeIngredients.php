<?php

namespace App\Form;

use App\Entity\Ingredient;
use App\Entity\GroceryList;
use App\Entity\Recipe;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class RecipeIngredients extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('groceryList', EntityType::class, [
                'label' => 'Your Lists',
                'class' => GroceryList::class,
                'choice_label' => 'title',
                'placeholder' => 'Sélectionnez une liste de courses',
                'required' => true,
                'query_builder' => function (EntityRepository $er) use ($options) {
                    return $er->createQueryBuilder('g')
                        ->where('g.user = :user')
                        ->setParameter('user', $options['data']['user']);
                },
                'by_reference' => false
            ])
            ->add('ingredients', EntityType::class, [
                'label' => 'Ingredients in',
                'class' => Ingredient::class,
                'choices' => $options['data']['recipe']->getIngredients(),
                'choice_label' => 'title',
                'multiple' => true,
                'expanded' => true,
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
