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
            $this->setGroceryListIngredient($ingredient,$groceryList,false,true);
        }

        $this->entityManager->flush();
        $ingredient->setTemporaryGroceryLists([]);
    }

     public function editIngredientsInGroceryLists($ingredient,$data) : void {

        // default delete all relations Ingredient / GroceryListIngredient
        $existingRelations = $this->entityManager->getRepository(GroceryListIngredient::class)
        ->findBy([
            'ingredient' => $ingredient
            // User ID maybe ? WIP
        ]);
        foreach ($existingRelations as $relation) {
            $this->entityManager->remove($relation);
        }
        $this->entityManager->flush();

        if (isset($data['groceryLists']) && !empty($data['groceryLists'])) {
            $selectedGroceryListIds = $data['groceryLists'];

            // rebuild relations Ingredient / GroceryListIngredient if some checked
            $groceryLists = $this->entityManager->getRepository(GroceryList::class)
                ->findBy(['id' => $selectedGroceryListIds]);

            foreach ($groceryLists as $groceryList) {
                $this->setGroceryListIngredient($ingredient,$groceryList,false,true);
            }

            $this->entityManager->flush();
            
        }
     }

     public function setGroceryListIngredient($ingredient,$groceryList,$activation,$inList) : void {
        $groceryListIngredient = new GroceryListIngredient();
        $groceryListIngredient->setIngredient($ingredient);
        $groceryListIngredient->setGroceryList($groceryList);
        $groceryListIngredient->setActivation($activation);
        $groceryListIngredient->setInList($inList);
        $this->entityManager->persist($groceryListIngredient);
     }
}