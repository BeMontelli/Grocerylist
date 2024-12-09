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
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UserCreateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('selectfile', FileAutocompleteField::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Select thumbnail',
                'attr' => [
                    'data-controller' => 'fileselector',
                    'class' => 'fileselector',
                ]
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
                'attr' => ['placeholder' => t('app.admin.userform.username.placeholder')],
                'label' => t('app.admin.userform.username')
            ])
            ->add('email',EmailType::class, [
                'empty_data' => '',
                'attr' => ['placeholder' => t('app.admin.userform.email.placeholder')],
                'label' => t('app.admin.userform.email')
            ])
            ->add('emailconfirm',EmailType::class, [
                'empty_data' => '',
                'mapped' => false,
                'attr' => ['placeholder' => t('app.admin.userform.email.confirm')],
                'label' => false
            ])
            ->add('password', PasswordType::class, [
                'label' => t('app.admin.userform.password'),
                'mapped' => false,
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

        $builder->addEventListener(\Symfony\Component\Form\FormEvents::POST_SUBMIT, function (\Symfony\Component\Form\FormEvent $event) {
            $form = $event->getForm();
        
            $email = $form->get('email')->getData();
            $emailConfirm = $form->get('emailconfirm')->getData();

            $password = $form->get('password')->getData();
            $passwordConfirm = $form->get('passwordconfirm')->getData();
        
            if ($email !== $emailConfirm) {
                $form->get('email')->addError(new \Symfony\Component\Form\FormError('The email addresses must match.'));
            }
        
            if ($password !== $passwordConfirm) {
                $form->get('password')->addError(new \Symfony\Component\Form\FormError('The passwords must match.'));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
