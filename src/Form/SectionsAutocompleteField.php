<?php

namespace App\Form;

use App\Entity\Section;
use App\Repository\SectionRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\User;

#[AsEntityAutocompleteField]
class SectionsAutocompleteField  extends AbstractType
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
            'class' => Section::class,
            'choice_label' => 'title',
            'multiple' => true,
            'expanded' => false,
            'required' => false,
            'label' => 'Sections',
            'placeholder' => 'Select section(s)',
            'query_builder' => function(SectionRepository $sectionRepository) use ($user) {
                return $sectionRepository->findForUser($user);
            },
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
