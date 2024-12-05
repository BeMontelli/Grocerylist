<?php

namespace App\Form;

use App\Entity\File;
use App\Repository\FileRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\User;

#[AsEntityAutocompleteField]
class FileAutocompleteField  extends AbstractType
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
            'class' => File::class,
            'choice_label' => 'url',
            'required' => false,
            'label' => 'Files',
            'placeholder' => 'Select file',
            'query_builder' => function(FileRepository $fileRepository) use ($user) {
                return $fileRepository->findForUser($user);
            },
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
