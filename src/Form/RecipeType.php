<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\File;
use App\Entity\Ingredient;
use App\Entity\Recipe;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\UX\Dropzone\Form\DropzoneType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Form\CategoryAutocompleteField;
use App\Form\IngredientsAutocompleteField;
use App\Form\FileAutocompleteField;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints\Image;
use App\Repository\IngredientRepository;

class RecipeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Recipe $recipe */
        $recipe = $options['data'];
        $builder
            ->add('title',TextType::class, [
                'empty_data' => '',
                'label' => 'Recipe title'
            ])
            ->add('slug',TextType::class, [
                'empty_data' => '',
                'required' => false,
                'label' => 'Slug'
            ])
            ->add('selectfile', FileAutocompleteField::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Select thumbnail',
                'attr' => [
                    'data-controller' => 'fileselector',
                    'class' => 'fileselector',
                ],
                'data' => $recipe->getThumbnail() ? $recipe->getThumbnail() : null,
            ])
            ->add('uploadfile', DropzoneType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Image()
                ],
                'label' => 'Upload thumbnail'
            ])
            ->add('category', CategoryAutocompleteField::class)
            ->add('ingredients', IngredientsAutocompleteField::class)
            ->add('content', TextareaType::class, [
                'empty_data' => '',
                'required' => false,
                'label' => 'Content',
                'attr' => [
                    'data-controller' => 'wysiwyg'
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save Recipe'
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT,$this->autoSlug(...))
            ->addEventListener(FormEvents::POST_SUBMIT,$this->autoTimestamps(...))
        ;
    }

    public function autoSlug(PreSubmitEvent $event) : void {
        $slugger = new AsciiSlugger();
        $data = $event->getData();

        $data['slug'] = strtolower($slugger->slug((!empty($data['slug'])) ? $data['slug'] : $data['title']));

        $event->setData($data);
    }

    public function autoTimestamps(PostSubmitEvent $event) : void {
        $data = $event->getData();
        if(!$data instanceof Recipe) return;

        if(!$data->getId()) $data->setCreatedAt(new \DateTimeImmutable());
        $data->setUpdatedAt(new \DateTimeImmutable());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recipe::class,
        ]);
    }
}
