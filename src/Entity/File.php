<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Repository\FileRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use ApiPlatform\OpenApi\Model;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: FileRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['read:File:collection']],
    denormalizationContext: ['groups' => ['write:File']],
    types: ['https://schema.org/MediaObject'],
    outputFormats: ['jsonld' => ['application/ld+json']],
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['read:File:collection']]),
        new Get(normalizationContext: ['groups' => ['read:File:collection', 'read:File:item']]),
        new Post(
            normalizationContext: ['groups' => ['read:File:collection']],
            inputFormats: ['multipart' => ['multipart/form-data']],
            openapi: new Model\Operation(
                requestBody: new Model\RequestBody(
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'file' => [
                                        'type' => 'string',
                                        'format' => 'binary'
                                    ]
                                ]
                            ]
                        ]
                    ])
                )
            )
        ),
        new Delete(),
    ],
    validationContext: ['groups' => ['Default', 'api']],
)]
class File
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read:File:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read:File:collection', 'write:File'])]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read:File:collection', 'write:File'])]
    private ?string $extension = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read:File:collection', 'write:File'])]
    #[ApiProperty(writable: false)]
    private ?string $url = null;

    #[ORM\ManyToOne(inversedBy: 'files')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['read:File:collection', 'write:File'])]
    private ?User $user = null;
    
    #[Vich\UploadableField(mapping: 'media_object', fileNameProperty: 'filePath')]
    #[Assert\NotNull(groups: ['api'])]
    public ?File $file = null;

    /**
     * @var Collection<int, Recipe>
     */
    #[ORM\OneToMany(targetEntity: Recipe::class, mappedBy: 'thumbnail')]
    #[Groups(['read:File:item', 'write:File'])]
    private Collection $recipes;

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

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setExtension(string $extension): static
    {
        $this->extension = $extension;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
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
            $recipe->setThumbnail($this);
        }

        return $this;
    }

    public function removeRecipe(Recipe $recipe): static
    {
        if ($this->recipes->removeElement($recipe)) {
            // set the owning side to null (unless already changed)
            if ($recipe->getThumbnail() === $this) {
                $recipe->setThumbnail(null);
            }
        }

        return $this;
    }
}
