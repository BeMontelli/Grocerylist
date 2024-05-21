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
use function Symfony\Component\Translation\t;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',TextType::class, [
                'empty_data' => '',
                'attr' => ['placeholder' => t('app.front.form.name.placeholder')],
                'label' => t('app.front.form.name')
            ])
            ->add('email',EmailType::class, [
                'empty_data' => '',
                'attr' => ['placeholder' => t('app.front.form.email.placeholder')],
                'label' => t('app.front.form.email')
            ])
            ->add('message',TextareaType::class, [
                'required' => false,
                'empty_data' => '',
                'attr' => ['placeholder' => t('app.front.form.message.placeholder')],
                'label' => t('app.front.form.message')
            ])
            ->add('save', SubmitType::class, [
                'label' => t('app.front.form.send')
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
