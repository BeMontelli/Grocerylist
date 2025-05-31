<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\ApiProperty;
use App\Repository\IngredientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: IngredientRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['read:Ingredient:collection']],
    denormalizationContext: ['groups' => ['write:Ingredient']],
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['read:Ingredient:collection']]),
        new Get(normalizationContext: ['groups' => ['read:Ingredient:collection']]),
        new Post(normalizationContext: ['groups' => ['read:Ingredient:collection']]),
        new Put(),
        new Delete(),
    ]
)]
class Ingredient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read:Ingredient:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\Sequentially([
        new Assert\NotBlank(),
        new Assert\Length(min: 3),
    ])]
    #[Groups(['read:Ingredient:collection', 'write:Ingredient'])]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Assert\Sequentially([
        new Assert\Length(min: 3),
        new Assert\Regex(
            pattern: "/^[a-z0-9]+(?:-[a-z0-9]+)*$/",
            message: "The slug should only contain lowercase letters, numbers, and dashes, and should start and end with a letter or number."
        ),
    ])]
    #[ApiProperty(example: 'slug-example')]
    #[Groups(['read:Ingredient:collection', 'write:Ingredient'])]
    private ?string $slug = null;

    #[ORM\Column]
    #[Groups(['read:Ingredient:collection', 'write:Ingredient'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['read:Ingredient:collection', 'write:Ingredient'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, Recipe>
     */
    #[ORM\ManyToMany(targetEntity: Recipe::class, mappedBy: 'ingredients')]
    #[Groups(['read:Ingredient:collection', 'write:Ingredient'])]
    private Collection $recipes;

    #[ORM\ManyToOne(inversedBy: 'ingredients', cascade: ['persist'])]
    #[Groups(['read:Ingredient:collection', 'write:Ingredient'])]
    private ?Section $section = null;

    #[ORM\ManyToOne(inversedBy: 'ingredients')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['read:Ingredient:collection', 'write:Ingredient'])]
    private ?User $user = null;

    /**
     * @var Collection<int, GroceryListIngredient>
     */
    #[ORM\OneToMany(targetEntity: GroceryListIngredient::class, mappedBy: 'ingredient', orphanRemoval: true)]
    #[Groups(['read:Ingredient:collection', 'write:Ingredient'])]
    private Collection $groceryListIngredients;

    /**
     * @var array
     */
    private array $temporaryGroceryLists = [];

    #[ORM\Column]
    private ?bool $availableRecipe = null;

    public function __construct()
    {
        $this->recipes = new ArrayCollection();
        $this->groceryListIngredients = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, Recipe>
     */
    public function getRecipes(): Collection
    {
        return $this->recipes;
    }

    public function addRecipe(Recipe $recipe): static
    {
        if (!$this->recipes->contains($recipe)) {
            $this->recipes->add($recipe);
            $recipe->addIngredient($this);
        }

        return $this;
    }

    public function removeRecipe(Recipe $recipe): static
    {
        if ($this->recipes->removeElement($recipe)) {
            $recipe->removeIngredient($this);
        }

        return $this;
    }

    public function getSection(): ?Section
    {
        return $this->section;
    }

    public function setSection(?Section $section): static
    {
        $this->section = $section;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, GroceryListIngredient>
     */
    public function getGroceryListIngredients(): Collection
    {
        return $this->groceryListIngredients;
    }

    public function addGroceryListIngredient(GroceryListIngredient $groceryListIngredient): static
    {
        if (!$this->groceryListIngredients->contains($groceryListIngredient)) {
            $this->groceryListIngredients->add($groceryListIngredient);
            $groceryListIngredient->setIngredient($this);
        }

        return $this;
    }

    public function removeGroceryListIngredient(GroceryListIngredient $groceryListIngredient): static
    {
        if ($this->groceryListIngredients->removeElement($groceryListIngredient)) {
            // set the owning side to null (unless already changed)
            if ($groceryListIngredient->getIngredient() === $this) {
                $groceryListIngredient->setIngredient(null);
            }
        }

        return $this;
    }

    public function setTemporaryGroceryLists(array $groceryLists): void
    {
        $this->temporaryGroceryLists = $groceryLists;
    }

    public function getTemporaryGroceryLists(): array
    {
        return $this->temporaryGroceryLists;
    }

    public function isAvailableRecipe(): ?bool
    {
        return $this->availableRecipe;
    }

    public function setAvailableRecipe(bool $availableRecipe): static
    {
        $this->availableRecipe = $availableRecipe;

        return $this;
    }
}
