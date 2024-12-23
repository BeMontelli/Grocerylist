<?php

namespace App\Form;

use App\Entity\Recipe;
use App\Repository\RecipeRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\User;

#[AsEntityAutocompleteField]
class RecipesAutocompleteField extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $resolver->setDefaults([
            'label' => 'Recipes related',
            'class' => Recipe::class,
            'choice_label' => 'title',
            'multiple' => true,
            'expanded' => false,
            'required' => false,
            'mapped' => true, // Not mapping to entity*/
            'query_builder' => function (RecipeRepository $er) use ($user) {
                return $er->findAllByUser($user);
            },
            'by_reference' => false,
            'data' => [],
            'attr' => ['class' => 'recipe__fields']
        ]);

    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
