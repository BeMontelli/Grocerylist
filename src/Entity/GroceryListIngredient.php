<?php

namespace App\Entity;

use App\Repository\GroceryListIngredientRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GroceryListIngredientRepository::class)]
class GroceryListIngredient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'yes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?GroceryList $groceryList = null;

    #[ORM\ManyToOne(inversedBy: 'groceryListIngredients')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ingredient $Ingredient = null;

    #[ORM\Column]
    private ?bool $activation = null;

    #[ORM\Column]
    private ?bool $inList = null;

    #[ORM\ManyToOne(inversedBy: 'yes')]
    private ?Recipe $Recipe = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGroceryList(): ?GroceryList
    {
        return $this->groceryList;
    }

    public function setGroceryList(?GroceryList $groceryList): static
    {
        $this->groceryList = $groceryList;

        return $this;
    }

    public function getIngredient(): ?Ingredient
    {
        return $this->Ingredient;
    }

    public function setIngredient(?Ingredient $Ingredient): static
    {
        $this->Ingredient = $Ingredient;

        return $this;
    }

    public function isActivation(): ?bool
    {
        return $this->activation;
    }

    public function setActivation(bool $activation): static
    {
        $this->activation = $activation;

        return $this;
    }

    public function isInList(): ?bool
    {
        return $this->inList;
    }

    public function setInList(bool $inList): static
    {
        $this->inList = $inList;

        return $this;
    }

    public function getRecipe(): ?Recipe
    {
        return $this->Recipe;
    }

    public function setRecipe(?Recipe $Recipe): static
    {
        $this->Recipe = $Recipe;

        return $this;
    }
}