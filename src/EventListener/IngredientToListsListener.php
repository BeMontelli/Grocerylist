<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Form\Event\SubmitEvent;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Ingredient;
use App\Entity\GroceryList;
use App\Entity\GroceryListIngredient;

final class IngredientToListsListener
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        /** @var Ingredient $ingredient */
        $ingredient = $args->getObject();
        
        if (!$ingredient instanceof Ingredient  || !$ingredient->getId()) {
            return;
        }

        // Récupérez les GroceryLists temporairement enregistrées et traitez-les
        $selectedGroceryListIds = $ingredient->getTemporaryGroceryLists();

        // Logic to service ? WIP
        if (isset($selectedGroceryListIds) && !empty($selectedGroceryListIds)) {
            
            // build relations Ingredient / GroceryListIngredient if some checked
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
            }
            
        }

        $ingredient->setTemporaryGroceryLists([]);
    }
}
