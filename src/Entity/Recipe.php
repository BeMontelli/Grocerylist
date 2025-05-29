<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiProperty;
use App\Repository\RecipeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\File;

#[ORM\Entity(repositoryClass: RecipeRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['recipe:read','*:read']],
    denormalizationContext: ['groups' => ['recipe:write','*:write']]
)]
class Recipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['recipe:read','recipe:write','*:read','*:write'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\Sequentially([
        new Assert\NotBlank(),
        new Assert\Length(min: 4),
    ])]
    #[Groups(['recipe:read','recipe:write'])]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Assert\Sequentially([
        new Assert\Length(min: 4),
        new Assert\Regex(
            pattern: "/^[a-z0-9]+(?:-[a-z0-9]+)*$/",
            message: "The slug should only contain lowercase letters, numbers, and dashes, and should start and end with a letter or number."
        ),
    ])]
    #[Groups(['recipe:read','recipe:write'])]
    #[ApiProperty(example: 'slug-example')]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['recipe:read','recipe:write'])]
    private ?string $content = null;

    #[ORM\Column]
    #[Groups(['recipe:read','recipe:write'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['recipe:read','recipe:write'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'recipes', cascade: ['persist'])]
    #[Groups(['recipe:read','recipe:write'])]
    private ?Category $category = null;

    /**
     * @var Collection<int, Ingredient>
     */
    #[ORM\ManyToMany(targetEntity: Ingredient::class, inversedBy: 'recipes')]
    #[Groups(['recipe:read','recipe:write'])]
    private Collection $ingredients;

    #[ORM\ManyToOne(inversedBy: 'recipes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['recipe:read','recipe:write'])]
    private ?User $user = null;

    /**
     * @var Collection<int, GroceryList>
     */
    #[ORM\ManyToMany(targetEntity: GroceryList::class, mappedBy: 'recipes')]
    #[Groups(['recipe:read','recipe:write'])]
    private Collection $groceryLists;

    /**
     * @var Collection<int, GroceryListIngredient>
     */
    #[ORM\OneToMany(targetEntity: GroceryListIngredient::class, mappedBy: 'recipe')]
    #[Groups(['recipe:read','recipe:write'])]
    private Collection $groceryListIngredients;

    #[ORM\ManyToOne(targetEntity: File::class, inversedBy: 'recipes')]
    #[ORM\JoinColumn(name: 'thumbnail_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[Groups(['recipe:read','recipe:write'])]
    private ?File $thumbnail = null;

    public function __construct()
    {
        $this->ingredients = new ArrayCollection();
        $this->groceryLists = new ArrayCollection();
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, Ingredient>
     */
    public function getIngredients(): Collection
    {
        return $this->ingredients;
    }

    public function addIngredient(Ingredient $ingredient): static
    {
        if (!$this->ingredients->contains($ingredient)) {
            $this->ingredients->add($ingredient);
        }

        return $this;
    }

    public function removeIngredient(Ingredient $ingredient): static
    {
        $this->ingredients->removeElement($ingredient);

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
     * @return Collection<int, GroceryList>
     */
    public function getGroceryLists(): Collection
    {
        return $this->groceryLists;
    }

    public function addGroceryList(GroceryList $groceryList): static
    {
        if (!$this->groceryLists->contains($groceryList)) {
            $this->groceryLists->add($groceryList);
            $groceryList->addRecipe($this);
        }

        return $this;
    }

    public function removeGroceryList(GroceryList $groceryList): static
    {
        if ($this->groceryLists->removeElement($groceryList)) {
            $groceryList->removeRecipe($this);
        }

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
            $groceryListIngredient->setRecipe($this);
        }

        return $this;
    }

    public function removeGroceryListIngredient(GroceryListIngredient $groceryListIngredient): static
    {
        if ($this->groceryListIngredients->removeElement($groceryListIngredient)) {
            // set the owning side to null (unless already changed)
            if ($groceryListIngredient->getRecipe() === $this) {
                $groceryListIngredient->setRecipe(null);
            }
        }

        return $this;
    }

    public function getThumbnail(): ?File
    {
        return $this->thumbnail;
    }

    public function setThumbnail(?File $thumbnail): static
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }
}
