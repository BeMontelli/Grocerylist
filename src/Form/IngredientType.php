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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Doctrine\ORM\EntityRepository;

class IngredientType extends AbstractType
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $ingredient = $options['data'];
        // Get GroceryLists linked to ingredient
        $groceryListsAssociated = $this->entityManager
            ->getRepository(GroceryList::class)
            ->createQueryBuilder('g')
            ->join(GroceryListIngredient::class, 'gli', 'WITH', 'gli.groceryList = g')
            ->where('gli.ingredient = :ingredient')
            ->setParameter('ingredient', $ingredient)
            ->getQuery()
            ->getResult();

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
            ->add('recipes', EntityType::class, [
                'label' => 'Recipes related',
                'class' => Recipe::class,
                'choice_label' => 'title',
                'multiple' => true,
                'expanded' => true,
                'by_reference' => false
            ])
            ->add('groceryLists', EntityType::class, [
                'label' => 'In grocery list(s) ?',
                'class' => GroceryList::class,
                'choice_label' => 'title',
                'mapped' => false, // Not mapping to entity
                'query_builder' => function (EntityRepository $er) use ($options) {
                    return $er->createQueryBuilder('g')
                        ->where('g.user = :user')
                        ->setParameter('user', $options['user']);
                },
                'multiple' => true,
                'expanded' => true,
                'by_reference' => false,
                'data' => $groceryListsAssociated 
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save Ingredient'
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT,$this->processGroceryLists(...))
            ->addEventListener(FormEvents::PRE_SUBMIT,$this->autoSlug(...))
            ->addEventListener(FormEvents::POST_SUBMIT,$this->autoTimestamps(...))
        ;
    }

    public function processGroceryLists(PreSubmitEvent $event) : void {
        
        $data = $event->getData();
        $form = $event->getForm();
        $ingredient = $form->getData();

        if (!$ingredient instanceof Ingredient) {
            return;
        }

        if (isset($data['groceryLists']) && !empty($data['groceryLists'])) {
            // default delete all relations Ingredient / GroceryListIngredient
            $selectedGroceryListIds = $data['groceryLists'];
            $existingRelations = $this->entityManager->getRepository(GroceryListIngredient::class)
            ->findBy([
                'ingredient' => $ingredient,
                'groceryList' => $selectedGroceryListIds
                // User ID maybe ? WIP
            ]);
            foreach ($existingRelations as $relation) {
                $this->entityManager->remove($relation);
            }
            $this->entityManager->flush();

            // rebuild relations Ingredient / GroceryListIngredient if some checked
            $selectedGroceryListIngredientsIds = [];
            $groceryLists = $this->entityManager->getRepository(GroceryList::class)
                ->findBy(['id' => $selectedGroceryListIds]);

            foreach ($groceryLists as $groceryList) {
                
                $groceryListIngredient = new GroceryListIngredient();
                $groceryListIngredient->setIngredient($ingredient);
                $groceryListIngredient->setGroceryList($groceryList);
                $groceryListIngredient->setActivation(false);
                $groceryListIngredient->setInList(true);

                $this->entityManager->persist($groceryListIngredient);
                $this->entityManager->flush();

                $selectedGroceryListIngredientsIds[] = $groceryListIngredient->getId();
            }
            
        } else {
            // if no lists, delete all ingredients relashionship
            $existingRelations = $this->entityManager->getRepository(GroceryListIngredient::class)
            ->findBy([
                'ingredient' => $ingredient
                // User ID maybe ? WIP
            ]);
            foreach ($existingRelations as $relation) {
                $this->entityManager->remove($relation);
            }
            $this->entityManager->flush();
        }

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
