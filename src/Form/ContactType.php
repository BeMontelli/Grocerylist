<?php

namespace App\Form;

use App\DTO\ContactDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',TextType::class, [
                'empty_data' => '',
                'attr' => ['placeholder' => 'John Doe'],
                'label' => 'Your name'
            ])
            ->add('email',EmailType::class, [
                'empty_data' => '',
                'attr' => ['placeholder' => 'johndoe@gmail.com'],
                'label' => 'Your email'
            ])
            ->add('message',TextareaType::class, [
                'required' => false,
                'empty_data' => '',
                'attr' => ['placeholder' => 'The message ...'],
                'label' => 'Your message'
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Send'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactDTO::class,
        ]);
    }
}
