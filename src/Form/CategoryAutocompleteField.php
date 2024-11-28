<?php

namespace App\Form;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\User;

#[AsEntityAutocompleteField]
class CategoryAutocompleteField extends AbstractType
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
            'class' => Category::class,
            'choice_label' => 'title',
            'label' => 'Recipe category',
            'placeholder' => 'Select a category',
            'query_builder' => function(CategoryRepository $categoryRepository) use ($user) {
                return $categoryRepository->findForUser($user);
            },
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
