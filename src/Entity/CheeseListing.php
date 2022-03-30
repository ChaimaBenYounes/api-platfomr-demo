<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use App\Repository\CheeseListingRepository;
use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    collectionOperations: [
        'get',
        'post' => ['security' => "is_granted('ROLE_USER')"]
    ],
    itemOperations: [
        'get',
        'put',
        'delete' => ['security' => "is_granted('ROLE_ADMIN')"]
    ],
    shortName: 'cheeses',
    attributes: [
    "pagination_items_per_page" => 10,
    ],
    denormalizationContext: ['groups' => 'cheese_listing:write', 'swagger_definition_name' => 'Write'],
    formats: [
        'json',
        'html',
        'csv'=> ['text/csv']
        ],
    normalizationContext: ['groups' => 'cheese_listing:read', 'cheese_listing:item:get', 'swagger_definition_name' => 'Read']
)]
#[ORM\Entity(repositoryClass: CheeseListingRepository::class)]
#[ApiFilter(BooleanFilter::class, properties : ['isPublished'])]
#[ApiFilter(SearchFilter::class, properties : [
    'title' => 'partial',
    'description' => 'partial',
    'owner' => 'partial'
])]
#[ApiFilter(RangeFilter::class, properties : ['price'])]
#[ApiFilter(PropertyFilter::class)]
class CheeseListing
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['cheese_listing:read'])]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Groups(['cheese_listing:read', 'cheese_listing:write', 'User:read', 'User:write'])]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: 'Your title name must be at least {{ limit }} characters long',
        maxMessage: 'Your title name cannot be longer than {{ limit }} characters',
    )]
    private $title;

    #[ORM\Column(type: 'text')]
    #[Groups(['cheese_listing:read'])]
    #[Assert\NotBlank]
    private $description;

    #[ORM\Column(type: 'integer')]
    #[Groups(['cheese_listing:read', 'cheese_listing:write', 'User:read', 'User:write'])]
    #[Assert\NotBlank]
    private $price;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    #[ORM\Column(type: 'boolean')]
    private $isPublished = false;


    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'cheeseListings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['cheese_listing:read', 'cheese_listing:write'])]
    #[Assert\Valid]
    private $owner;

    /**
     * CheeseListing constructor.
     * @param $createdAt
     */
    public function __construct(string $title = null)
    {
        $this->title = $title;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    #[Groups(['cheese_listing:read'])]
    public function getShortDescription(): ?string
    {
        if (strlen($this->description) < 40) {
            return $this->description;
        }

        return substr($this->description, 0, 40).'...';
    }

    #The description of the cheese as raw text.
    #[Groups(['cheese_listing:write', 'User:write'])]
    #[SerializedName (['description'])]
    public function setTextDescription(string $description): self
    {
        $this->description = nl2br($description);

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    # How long ago in text that this cheese listing was added.
    #[Groups(['cheese_listing:read'])]
    public function getCreatedAtAgo(): string
    {
        return Carbon::instance($this->getCreatedAt())->diffForHumans();
    }

    public function getIsPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }
}
