<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[ApiResource(
    normalizationContext: ['groups' => ['read:User:collection']],
    denormalizationContext: ['groups' => ['write:User']],
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['read:User:collection']]),
        new Get(normalizationContext: ['groups' => ['read:User:collection', 'read:User:item']]),
        new Post(normalizationContext: ['groups' => ['read:User:collection']]),
        new Put(),
        new Delete(),
    ]
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read:User:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Groups(['read:User:collection', 'write:User'])]
    private ?string $username = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Groups(['read:User:collection', 'write:User'])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Groups(['read:User:item', 'write:User'])]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read:User:collection', 'write:User'])]
    private ?string $email = null;

    #[ORM\Column]
    #[Groups(['read:User:item', 'write:User'])]
    private bool $isVerified = false;

    /**
     * @var Collection<int, GroceryList>
     */
    #[ORM\OneToMany(targetEntity: GroceryList::class, mappedBy: 'user', orphanRemoval: true)]
    #[Groups(['read:User:item'])]
    private Collection $groceryLists;

    /**
     * @var Collection<int, Recipe>
     */
    #[ORM\OneToMany(targetEntity: Recipe::class, mappedBy: 'user')]
    #[Groups(['read:User:item'])]
    private Collection $recipes;

    /**
     * @var Collection<int, Ingredient>
     */
    #[ORM\OneToMany(targetEntity: Ingredient::class, mappedBy: 'user')]
    #[Groups(['read:User:item'])]
    private Collection $ingredients;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\OneToMany(targetEntity: Category::class, mappedBy: 'user')]
    #[Groups(['read:User:item'])]
    private Collection $categories;

    /**
     * @var Collection<int, Section>
     */
    #[ORM\OneToMany(targetEntity: Section::class, mappedBy: 'user')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['read:User:item'])]
    private Collection $sections;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[Groups(['read:User:item'])]
    private ?GroceryList $current_grocery_list = null;

    /**
     * @var Collection<int, File>
     */
    #[ORM\OneToMany(targetEntity: File::class, mappedBy: 'user', orphanRemoval: true)]
    #[Groups(['read:User:item'])]
    private Collection $files;

    #[ORM\ManyToOne]
    #[Groups(['read:User:collection'])]
    private ?File $picture = null;

    public function __construct()
    {
        $this->groceryLists = new ArrayCollection();
        $this->recipes = new ArrayCollection();
        $this->ingredients = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->sections = new ArrayCollection();
        $this->files = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';
        /*if ($this->getId() === 1) {
            $roles[] = 'ROLE_ADMIN';
        }
        if ($this->isVerified()) {
            $roles[] = 'ROLE_VERIFIED';
        }*/

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

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
            $groceryList->setUser($this);
        }

        return $this;
    }

    public function removeGroceryList(GroceryList $groceryList): static
    {
        if ($this->groceryLists->removeElement($groceryList)) {
            // set the owning side to null (unless already changed)
            if ($groceryList->getUser() === $this) {
                $groceryList->setUser(null);
            }
        }

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
            $recipe->setUser($this);
        }

        return $this;
    }

    public function removeRecipe(Recipe $recipe): static
    {
        if ($this->recipes->removeElement($recipe)) {
            // set the owning side to null (unless already changed)
            if ($recipe->getUser() === $this) {
                $recipe->setUser(null);
            }
        }

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
            $ingredient->setUser($this);
        }

        return $this;
    }

    public function removeIngredient(Ingredient $ingredient): static
    {
        if ($this->ingredients->removeElement($ingredient)) {
            // set the owning side to null (unless already changed)
            if ($ingredient->getUser() === $this) {
                $ingredient->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->setUser($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        if ($this->categories->removeElement($category)) {
            // set the owning side to null (unless already changed)
            if ($category->getUser() === $this) {
                $category->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Section>
     */
    public function getSections(): Collection
    {
        return $this->sections;
    }

    public function addSection(Section $section): static
    {
        if (!$this->sections->contains($section)) {
            $this->sections->add($section);
            $section->setUser($this);
        }

        return $this;
    }

    public function removeSection(Section $section): static
    {
        if ($this->sections->removeElement($section)) {
            // set the owning side to null (unless already changed)
            if ($section->getUser() === $this) {
                $section->setUser(null);
            }
        }

        return $this;
    }

    public function getCurrentGroceryList(): ?GroceryList
    {
        return $this->current_grocery_list;
    }

    /**
     * method used to retrieve getcurrent_grocery_list for twig templates
     * used with : app.user.current_grocery_list
     * @return GroceryList|null
     */
    public function getcurrent_grocery_list(): ?GroceryList
    {
        return $this->current_grocery_list;
    }

    public function setCurrentGroceryList(?GroceryList $current_grocery_list): static
    {
        $this->current_grocery_list = $current_grocery_list;

        return $this;
    }

    /**
     * @return Collection<int, File>
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(File $file): static
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
            $file->setUser($this);
        }

        return $this;
    }

    public function getPicture(): ?File
    {
        return $this->picture;
    }

    public function setPicture(?File $picture): static
    {
        $this->picture = $picture;

        return $this;
    }
}
