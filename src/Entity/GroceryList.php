<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\ApiProperty;
use App\Repository\GroceryListRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: GroceryListRepository::class)]
#[UniqueEntity(fields: ['publicSlug'], message: 'app.admin.lists.publicslug.form.uniquealert', ignoreNull: true)]
#[ApiResource(
    normalizationContext: ['groups' => ['read:GroceryList:collection']],
    denormalizationContext: ['groups' => ['write:GroceryList']],
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['read:GroceryList:collection']]),
        new Get(normalizationContext: ['groups' => ['read:GroceryList:collection', 'read:GroceryList:item']]),
        new Post(normalizationContext: ['groups' => ['read:GroceryList:collection']]),
        new Put(),
        new Delete(),
    ]
)]
class GroceryList
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read:GroceryList:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read:GroceryList:collection', 'write:GroceryList'])]
    private ?string $title = null;

    #[ORM\Column]
    #[Groups(['read:GroceryList:collection', 'write:GroceryList'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['read:GroceryList:collection', 'write:GroceryList'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255)]
    #[ApiProperty(example: 'slug-example')]
    #[Groups(['read:GroceryList:collection', 'write:GroceryList'])]
    private ?string $slug = null;

    #[ORM\ManyToOne(inversedBy: 'groceryLists')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['read:GroceryList:collection', 'write:GroceryList'])]
    private ?User $user = null;

    /**
     * @var Collection<int, Recipe>
     */
    #[ORM\ManyToMany(targetEntity: Recipe::class, inversedBy: 'groceryLists')]
    #[Groups(['read:GroceryList:item', 'write:GroceryList'])]
    private Collection $recipes;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'current_grocery_list')]
    #[Groups(['read:GroceryList:item', 'write:GroceryList'])]
    private Collection $users;

    /**
     * @var Collection<int, GroceryListIngredient>
     */
    #[ORM\OneToMany(targetEntity: GroceryListIngredient::class, mappedBy: 'groceryList', orphanRemoval: true)]
    #[Groups(['read:GroceryList:item', 'write:GroceryList'])]
    private Collection $groceryListIngredients;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Expression(
        expression: "this.getPublicSlug() === null || this.getPublicSlug() === '' || (this.getPublicSlug() != null && this.getPublicSlugLength() >= 10)",
        message: 'app.admin.lists.publicslug.form.lengthalert'
    )]
    #[Assert\Regex(
        pattern: '/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
        message: 'app.admin.lists.publicslug.form.urlalert'
    )]
    #[Groups(['read:GroceryList:collection', 'write:GroceryList'])]
    private ?string $publicSlug = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['read:GroceryList:collection', 'write:GroceryList'])]
    private ?string $comments = null;

    public function __construct()
    {
        $this->recipes = new ArrayCollection();
        $this->users = new ArrayCollection();
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

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
        }

        return $this;
    }

    public function removeRecipe(Recipe $recipe): static
    {
        $this->recipes->removeElement($recipe);

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setCurrentGroceryList($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getCurrentGroceryList() === $this) {
                $user->setCurrentGroceryList(null);
            }
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
            $groceryListIngredient->setGroceryList($this);
        }

        return $this;
    }

    public function removeGroceryListIngredient(GroceryListIngredient $groceryListIngredient): static
    {
        if ($this->groceryListIngredients->removeElement($groceryListIngredient)) {
            // set the owning side to null (unless already changed)
            if ($groceryListIngredient->getGroceryList() === $this) {
                $groceryListIngredient->setGroceryList(null);
            }
        }

        return $this;
    }

    public function getPublicSlug(): ?string
    {
        return $this->publicSlug;
    }

    public function setPublicSlug(?string $publicSlug): static
    {
        $this->publicSlug = $publicSlug;

        return $this;
    }
    
    public function getPublicSlugLength(): int
    {
        return strlen($this->publicSlug);
    }

    public function getComments(): ?string
    {
        return $this->comments;
    }

    public function setComments(?string $comments): static
    {
        $this->comments = $comments;

        return $this;
    }
}
