<?php

namespace App\Form;

use App\Entity\GroceryList;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\UX\Dropzone\Form\DropzoneType;
use Symfony\Component\Validator\Constraints\Image;
use function Symfony\Component\Translation\t;

class UserEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $user */
        $user = $options['data'];
        $builder
            ->add('selectfile', FileAutocompleteField::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Select thumbnail',
                'attr' => [
                    'data-controller' => 'fileselector',
                    'class' => 'fileselector',
                ],
                'data' => $user->getPicture() ? $user->getPicture() : null,
            ])
            ->add('uploadfile', DropzoneType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Image()
                ],
                'label' => 'Upload thumbnail'
            ])
            ->add('username',TextType::class, [
                'empty_data' => '',
                'required' => false,
                'attr' => ['placeholder' => t('app.admin.userform.username.placeholder')],
                'label' => t('app.admin.userform.username')
            ])
            ->add('email',EmailType::class, [
                'empty_data' => '',
                'required' => false,
                'attr' => ['placeholder' => t('app.admin.userform.email.placeholder')],
                'label' => t('app.admin.userform.email')
            ])
            ->add('emailconfirm',EmailType::class, [
                'empty_data' => '',
                'mapped' => false,
                'required' => false,
                'attr' => ['placeholder' => t('app.admin.userform.email.confirm')],
                'label' => false
            ])
            ->add('password', PasswordType::class, [
                'label' => t('app.admin.userform.password'),
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'placeholder' => '**********'
                ],
                'toggle' => true,
                'constraints' => [
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('passwordconfirm', PasswordType::class, [
                'label' => false,
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'placeholder' => t('app.admin.userform.password.confirm')
                ],
                'toggle' => true,
                'constraints' => [
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => t('app.admin.userform.btn')
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
