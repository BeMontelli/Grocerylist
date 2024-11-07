<?php
namespace App\Service;

use App\Entity\Ingredient;
use App\Entity\GroceryList;
use App\Entity\GroceryListIngredient;
use Doctrine\ORM\EntityManagerInterface;

class GroceryListIngredientService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function linkIngredientToGroceryLists(Ingredient $ingredient, array $groceryListIds): void
    {
        if (empty($groceryListIds)) {
            return;
        }

        // Récupère les GroceryLists correspondant aux IDs
        $groceryLists = $this->entityManager->getRepository(GroceryList::class)
            ->findBy(['id' => $groceryListIds]);

        foreach ($groceryLists as $groceryList) {
            $groceryListIngredient = new GroceryListIngredient();
            $groceryListIngredient->setIngredient($ingredient);
            $groceryListIngredient->setGroceryList($groceryList);
            $groceryListIngredient->setActivation(false);
            $groceryListIngredient->setInList(true);

            $this->entityManager->persist($groceryListIngredient);
        }

        $this->entityManager->flush();
        $ingredient->setTemporaryGroceryLists([]);
    }
}