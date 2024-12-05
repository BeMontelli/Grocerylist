<?php

namespace App\Form;

use App\Entity\File;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType as SymfonyFileType;
use Symfony\UX\Dropzone\Form\DropzoneType;

class FilesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('files', DropzoneType::class, [
                'mapped' => false,
                'multiple' => true,
                'required' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => File::class,
        ]);
    }
}
