<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\Sequentially([
        new Assert\NotBlank(),
        new Assert\Length(min: 3),
    ])]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Sequentially([
        new Assert\Length(min: 3),
        new Assert\Regex(
            pattern: "/^[a-z0-9]+(?:-[a-z0-9]+)*$/",
            message: "The slug should only contain lowercase letters, numbers, and dashes, and should start and end with a letter or number."
        ),
    ])]
    private ?string $slug = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?user $user = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Group $product_group = null;

    /**
     * @var Collection<int, GroceryList>
     */
    #[ORM\ManyToMany(targetEntity: GroceryList::class, inversedBy: 'products')]
    private Collection $groceryLists;

    public function __construct()
    {
        $this->groceryLists = new ArrayCollection();
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

    public function setSlug(?string $slug): static
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

    public function getUser(): ?user
    {
        return $this->user;
    }

    public function setUser(?user $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getProductGroup(): ?Group
    {
        return $this->product_group;
    }

    public function setProductGroup(?Group $product_group): static
    {
        $this->product_group = $product_group;

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
        }

        return $this;
    }

    public function removeGroceryList(GroceryList $groceryList): static
    {
        $this->groceryLists->removeElement($groceryList);

        return $this;
    }
}
