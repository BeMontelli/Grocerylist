<?php
namespace App\Service;

use App\Entity\Ingredient;
use App\Entity\GroceryList;
use App\Entity\Recipe;
use App\Entity\GroceryListIngredient;
use App\Entity\Section;
use Doctrine\ORM\EntityManagerInterface;

class GroceryListIngredientService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getIngredientsStructured(array $groceryListIngredients): array {
        $structure = [];
        
        /** @var GroceryListIngredient $groceryListIngredient */
        foreach ($groceryListIngredients as $groceryListIngredient) {
            /** @var Ingredient $ingredient */
            $ingredient = $groceryListIngredient->getIngredient();
            $recipe = $groceryListIngredient->getRecipe();

            /** @var Section $section */
            $section = $ingredient->getSection();
            if(!empty($section)) {
                $sectionTitle = $section->getTitle();
                $this->entityManager->initializeObject($section);
                if(
                    !array_key_exists($sectionTitle,$structure) || 
                    !array_key_exists("ingredients",$structure[$sectionTitle])
                ) {
                    $structure[$sectionTitle] = [
                        "section" => $section,
                        "ingredients" => []
                    ];
                    $structure[$sectionTitle]["ingredients"][$ingredient->getTitle()] = [
                        "id" => $ingredient->getId(),
                        "title" => $ingredient->getTitle(),
                        "inList" => $groceryListIngredient->isInList(),
                        "activation" => $groceryListIngredient->isActive(),
                        "collection" => [$ingredient],
                        "recipes" => []
                    ];
                    if($groceryListIngredient->isInList() && $recipe) $structure[$sectionTitle]["ingredients"][$ingredient->getTitle()]["recipes"][] = $recipe;
                } else {
                    if(
                        !array_key_exists($ingredient->getTitle(),$structure[$sectionTitle]["ingredients"]) || 
                        !array_key_exists("collection",$structure[$sectionTitle]["ingredients"][$ingredient->getTitle()])
                    ) {
                        $structure[$sectionTitle]["ingredients"][$ingredient->getTitle()] = [
                            "id" => $ingredient->getId(),
                            "title" => $ingredient->getTitle(),
                            "inList" => $groceryListIngredient->isInList(),
                            "activation" => $groceryListIngredient->isActive(),
                            "collection" => [$ingredient],
                            "recipes" => []
                        ];
                        if($groceryListIngredient->isInList() && $recipe) $structure[$sectionTitle]["ingredients"][$ingredient->getTitle()]["recipes"][] = $recipe;
                    } else {
                        if($groceryListIngredient->isInList()){
                            $structure[$sectionTitle]["ingredients"][$ingredient->getTitle()]['inList'] = true;
                            if($recipe) $structure[$sectionTitle]["ingredients"][$ingredient->getTitle()]["recipes"][] = $recipe;
                        }
                        $structure[$sectionTitle]["ingredients"][$ingredient->getTitle()]["collection"][] = $ingredient;
                    }
                }
            }
        }

        return $structure;
    }

    public function linkIngredientToGroceryLists(Ingredient $ingredient, ?Recipe $recipe, array $groceryListIds): void
    {
        if (empty($groceryListIds)) {
            return;
        }

        // Récupère les GroceryLists correspondant aux IDs
        $groceryLists = $this->entityManager->getRepository(GroceryList::class)
            ->findBy(['id' => $groceryListIds]);

        foreach ($groceryLists as $groceryList) {
            $this->setGroceryListIngredient($ingredient,$recipe,$groceryList,false,true);
        }

        $this->entityManager->flush();
        $ingredient->setTemporaryGroceryLists([]);
    }

    public function removeRecipeIngredientsInGroceryList(Recipe $recipe, GroceryList $groceryList) : void {
        $existingRelations = $this->entityManager->getRepository(GroceryListIngredient::class)
        ->findBy([
            'recipe' => $recipe,
            'groceryList' => $groceryList,
            // User ID maybe ? WIP
        ]);
        foreach ($existingRelations as $relation) {
            $this->entityManager->remove($relation);
        }
        $this->entityManager->flush();
    }

     public function editIngredientsInGroceryLists(Ingredient $ingredient, ?Recipe $recipe, $arrGroceryLists,$inList = true) : void {

        // default delete all relations Ingredient / GroceryListIngredient
        $existingRelations = $this->entityManager->getRepository(GroceryListIngredient::class)
        ->findBy([
            'ingredient' => $ingredient,
            'recipe' => $recipe,
            // User ID maybe ? WIP
        ]);
        foreach ($existingRelations as $relation) {
            $this->entityManager->remove($relation);
        }
        $this->entityManager->flush();

        if (isset($arrGroceryLists['groceryLists']) && !empty($arrGroceryLists['groceryLists'])) {
            $selectedGroceryListIds = $arrGroceryLists['groceryLists'];

            // rebuild relations Ingredient / GroceryListIngredient if some checked
            $groceryLists = $this->entityManager->getRepository(GroceryList::class)
                ->findBy(['id' => $selectedGroceryListIds]);

            foreach ($groceryLists as $groceryList) {
                $this->setGroceryListIngredient($ingredient,$recipe,$groceryList,false,$inList);
            }

            $this->entityManager->flush();
            
        }
     }

     public function setGroceryListIngredient($ingredient,$recipe,$groceryList,$activation,$inList) : void {
        $groceryListIngredient = new GroceryListIngredient();
        $groceryListIngredient->setIngredient($ingredient);
        $groceryListIngredient->setGroceryList($groceryList);
        $groceryListIngredient->setRecipe($recipe);
        $groceryListIngredient->setActivation($activation);
        $groceryListIngredient->setInList($inList);
        $this->entityManager->persist($groceryListIngredient);
     }
}