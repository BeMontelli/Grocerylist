<?php

namespace App\Form;

use App\Entity\Ingredient;
use App\Repository\IngredientRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\User;

#[AsEntityAutocompleteField]
class IngredientsAutocompleteField extends AbstractType
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
            'class' => Ingredient::class,
            'label' => 'Ingredients in',
            'placeholder' => 'Choose ingredient(s)',
            'choice_label' => 'title',
            'multiple' => true,
            'query_builder' => function(IngredientRepository $ingredientRepository) use ($user) {
                return $ingredientRepository->findAvailableForRecipeAndUser($user);
            },
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
