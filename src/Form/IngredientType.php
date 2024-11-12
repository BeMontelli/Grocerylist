<?php

namespace App\Form;

use App\Entity\GroceryList;
use App\Entity\GroceryListIngredient;
use App\Entity\Ingredient;
use App\Entity\Recipe;
use App\Entity\Section;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Doctrine\ORM\EntityRepository;
use App\Repository\GroceryListRepository;
use App\Service\GroceryListIngredientService;

class IngredientType extends AbstractType
{
    private EntityManagerInterface $entityManager;
    private GroceryListRepository $groceryListRepository;
    private GroceryListIngredientService $groceryListIngredientService;

    public function __construct(EntityManagerInterface $entityManager, GroceryListRepository $groceryListRepository, GroceryListIngredientService $groceryListIngredientService)
    {
        $this->entityManager = $entityManager;
        $this->groceryListRepository = $groceryListRepository;
        $this->groceryListIngredientService = $groceryListIngredientService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $ingredient = $options['data'];
        // Get GroceryLists linked to ingredient
        $groceryListsAssociated = [];
        if($ingredient->getId()) {
            $groceryListsAssociated = $this->groceryListRepository->getIngredientGroceryLists($ingredient);
        }

        $builder
            ->add('title',TextType::class, [
                'empty_data' => '',
                'label' => 'Ingredient title'
            ])
            ->add('slug',TextType::class, [
                'empty_data' => '',
                'required' => false,
                'label' => 'Slug'
            ])
            ->add('section', EntityType::class, [
                'class' => Section::class,
                'label' => 'Ingredient section',
                'choice_label' => 'title',
                'placeholder' => 'Select a section',
            ])
            ->add('availableRecipe', CheckboxType::class, [
                'label' => 'Can be choosed in a Recipe ?',
                'required' => false,
                'attr' => ['class' => 'recipes__check'],
            ])
            ->add('recipes', EntityType::class, [
                'label' => 'Recipes related',
                'class' => Recipe::class,
                'choice_label' => 'title',
                'multiple' => true,
                'expanded' => true,
                'by_reference' => false,
                'attr' => ['class' => 'recipe__fields'],
            ])
            ->add('groceryLists', EntityType::class, [
                'label' => 'In grocery list(s) ?',
                'class' => GroceryList::class,
                'choice_label' => 'title',
                'mapped' => false, // Not mapping to entity
                'query_builder' => function (GroceryListRepository $er) use ($options) {
                    return $er->findGroceryListForUser($options['user']);
                },
                'multiple' => true,
                'expanded' => true,
                'by_reference' => false,
                'data' => $groceryListsAssociated 
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save Ingredient'
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT,$this->processGroceryListsPreSubmit(...))
            ->addEventListener(FormEvents::PRE_SUBMIT,$this->autoSlug(...))
            ->addEventListener(FormEvents::POST_SUBMIT,$this->autoTimestamps(...))
        ;
    }

    public function processGroceryListsPreSubmit(PreSubmitEvent $event) : void {
        
        $data = $event->getData();
        $form = $event->getForm();
        $ingredient = $form->getData();

        if (!$ingredient instanceof Ingredient) return;
        // cancel process if $ingredient not ingredient

        // if $ingredient do not have ID, it does not exist => create
        if (!$ingredient->getId()) {
            // not already created on /create 
            if (isset($data['groceryLists']) && !empty($data['groceryLists'])) {
                // TemporaryGroceryLists to store data groceryLists && then expoit in controller where built form
                $ingredient->setTemporaryGroceryLists($data['groceryLists']);
            }
            return;
        }

        // if $ingredient already have ID, it exist => edit
        $this->groceryListIngredientService->editIngredientsInGroceryLists(
            $ingredient,
            $data
        );

        $event->setData($data);
    }

    public function autoSlug(PreSubmitEvent $event) : void {
        $slugger = new AsciiSlugger();
        $data = $event->getData();

        $data['slug'] = strtolower($slugger->slug((!empty($data['slug'])) ? $data['slug'] : $data['title']));

        $event->setData($data);
    }

    public function autoTimestamps(PostSubmitEvent $event) : void {
        $data = $event->getData();
        if(!$data instanceof Ingredient) return;

        if(!$data->getId()) $data->setCreatedAt(new \DateTimeImmutable());
        $data->setUpdatedAt(new \DateTimeImmutable());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ingredient::class,
        ]);
        $resolver->setDefined(['user']);
    }
}
