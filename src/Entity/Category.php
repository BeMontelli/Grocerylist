<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\ApiProperty;
use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['read:Category:collection']],
    denormalizationContext: ['groups' => ['write:Category']],
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['read:Category:collection']]),
        new Get(normalizationContext: ['groups' => ['read:Category:collection', 'read:Category:item']]),
        new Post(normalizationContext: ['groups' => ['read:Category:collection']]),
        new Put(),
        new Delete(),
    ]
)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read:Category:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\Sequentially([
        new Assert\NotBlank(),
        new Assert\Length(min: 4),
    ])]
    #[Groups(['read:Category:collection', 'write:Category'])]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Assert\Sequentially([
        new Assert\Length(min: 4),
        new Assert\Regex(
            pattern: "/^[a-z0-9]+(?:-[a-z0-9]+)*$/",
            message: "The slug should only contain lowercase letters, numbers, and dashes, and should start and end with a letter or number."
        ),
    ])]
    #[Groups(['read:Category:collection', 'write:Category'])]
    #[ApiProperty(example: 'slug-example')]
    private ?string $slug = null;

    #[ORM\Column]
    #[Groups(['read:Category:collection', 'write:Category'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['read:Category:collection', 'write:Category'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(targetEntity: Recipe::class, mappedBy: 'category', cascade: ['remove'], orphanRemoval: true)]
    #[Groups(['read:Category:item', 'write:Category'])]
    private Collection $recipes;

    #[ORM\ManyToOne(inversedBy: 'categories')]
    #[Groups(['read:Category:collection', 'write:Category'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['read:Category:collection', 'write:Category'])]
    private ?int $position = null;

    public function __construct()
    {
        $this->recipes = new ArrayCollection();
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
            $recipe->setCategory($this);
        }

        return $this;
    }

    public function removeRecipe(Recipe $recipe): static
    {
        if ($this->recipes->removeElement($recipe)) {
            // set the owning side to null (unless already changed)
            if ($recipe->getCategory() === $this) {
                $recipe->setCategory(null);
            }
        }

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

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): static
    {
        $this->position = $position;

        return $this;
    }
}
