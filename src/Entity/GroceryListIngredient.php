<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\GroceryListIngredientRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: GroceryListIngredientRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['grocery_list_ingredient:read','*:read']],
    denormalizationContext: ['groups' => ['grocery_list_ingredient:write','*:write']]
)]
class GroceryListIngredient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['grocery_list_ingredient:read','grocery_list_ingredient:write','*:read','*:write'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'groceryListIngredients')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['grocery_list_ingredient:read','grocery_list_ingredient:write'])]
    private ?GroceryList $groceryList = null;

    #[ORM\ManyToOne(inversedBy: 'groceryListIngredients')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['grocery_list_ingredient:read','grocery_list_ingredient:write'])]
    private ?Ingredient $ingredient = null;

    #[ORM\Column]
    #[Groups(['grocery_list_ingredient:read','grocery_list_ingredient:write'])]
    private ?bool $activation = null;

    #[ORM\Column]
    #[Groups(['grocery_list_ingredient:read','grocery_list_ingredient:write'])]
    private ?bool $inList = null;

    #[ORM\ManyToOne(inversedBy: 'groceryListIngredients')]
    #[Groups(['grocery_list_ingredient:read','grocery_list_ingredient:write'])]
    private ?Recipe $recipe = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['grocery_list_ingredient:read','grocery_list_ingredient:write'])]
    private ?string $comment = null;

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

    public function getIngredient(): ?ingredient
    {
        return $this->ingredient;
    }

    public function setIngredient(?ingredient $ingredient): static
    {
        $this->ingredient = $ingredient;

        return $this;
    }

    public function isActive(): ?bool
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
        return $this->recipe;
    }

    public function setRecipe(?Recipe $recipe): static
    {
        $this->recipe = $recipe;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }
}
