<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Repository\GroceryListIngredientRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: GroceryListIngredientRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['read:GroceryListIngredient:collection']],
    denormalizationContext: ['groups' => ['write:GroceryListIngredient']],
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['read:GroceryListIngredient:collection']]),
        new Get(normalizationContext: ['groups' => ['read:GroceryListIngredient:collection', 'read:GroceryListIngredient:item']]),
        new Post(normalizationContext: ['groups' => ['read:GroceryListIngredient:collection']]),
        new Put(),
        new Delete(),
    ]
)]
class GroceryListIngredient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read:GroceryListIngredient:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'groceryListIngredients')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['read:GroceryListIngredient:collection', 'write:GroceryListIngredient'])]
    private ?GroceryList $groceryList = null;

    #[ORM\ManyToOne(inversedBy: 'groceryListIngredients')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['read:GroceryListIngredient:collection', 'write:GroceryListIngredient'])]
    private ?Ingredient $ingredient = null;

    #[ORM\Column]
    #[Groups(['read:GroceryListIngredient:collection', 'write:GroceryListIngredient'])]
    private ?bool $activation = null;

    #[ORM\Column]
    #[Groups(['read:GroceryListIngredient:collection', 'write:GroceryListIngredient'])]
    private ?bool $inList = null;

    #[ORM\ManyToOne(inversedBy: 'groceryListIngredients')]
    #[Groups(['read:GroceryListIngredient:collection', 'write:GroceryListIngredient'])]
    private ?Recipe $recipe = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['read:GroceryListIngredient:collection', 'write:GroceryListIngredient'])]
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
