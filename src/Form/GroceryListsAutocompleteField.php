<?php

namespace App\Form;

use App\Entity\GroceryList;
use App\Repository\GroceryListRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\User;

#[AsEntityAutocompleteField]
class GroceryListsAutocompleteField extends AbstractType
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
            'label' => 'In grocery list(s) ?',
            'class' => GroceryList::class,
            'choice_label' => 'title',
            'mapped' => false, // Not mapping to entity*/
            'query_builder' => function (GroceryListRepository $er) use ($user) {
                return $er->findGroceryListForUser($user);
            },
            'multiple' => true,
            'expanded' => false,
            'by_reference' => false,
            'data' => []
        ]);

    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
