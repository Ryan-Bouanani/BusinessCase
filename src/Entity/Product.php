<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\ProductRepository;
use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[UniqueEntity('name')]
#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ApiResource(
    attributes: [
        "security" => "is_granted('ROLE_STATS')",
        "security_message" => "Accès refusé",
    ],
    collectionOperations: [
    ],
    itemOperations: [
    ],
)]
#[ORM\HasLifecycleCallbacks]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    use VapeurIshEntity;

    #[ORM\Column(type: Types::TEXT)]
    #[
        Assert\NotBlank([
            'message' => "Veuillez remplir tout les champs."
        ]),
        Assert\Length([
            'min' => 10,
            'max' => 1000,
            'minMessage' => 'Veuillez entrer une description contenant au minimum {{ limit }} caractères',
            'maxMessage' => 'Veuillez entrer une description contenant au maximum {{ limit }} caractères',
        ]),
    ]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 7, scale: 2)]
    #[
        Assert\NotBlank([
            'message' => "Veuillez remplir tout les champs."
        ]),
        Assert\PositiveOrZero(),
        Assert\Range(
            min: 0,
            max: 99000.99,
            notInRangeMessage: 'Le prix HT doit être plus grand que 0 et inferieur à 100 000',
        )
    ]
    private ?string $priceExclVat = null;

    #[ORM\Column]
    #[
        Assert\NotNull(),
    ]
    private ?bool $active = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[
        Assert\NotBlank([
            'message' => "Veuillez remplir tout les champs."
        ]),
    ]
    private ?\DateTimeInterface $dateAdded = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 4, scale: 2)]
    #[
        Assert\NotBlank([
            'message' => "Veuillez remplir tout les champs."
        ]),
    ]
    private ?string $tva = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    #[
        Assert\NotBlank([
            'message' => "Veuillez remplir tout les champs."
        ]),
    ]
    private ?Brand $brand = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?Promotion $promotion = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    #[
        Assert\NotBlank([
            'message' => "Veuillez remplir tout les champs."
        ]),
    ]
    private ?Category $category = null;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: Image::class, cascade: [
        'persist',
        'remove'
    ])]
    #[
        Assert\NotBlank([
            'message' => "Veuillez remplir tout les champs."
        ]),
    ]
    private Collection $images;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: Review::class, cascade: [
        'remove'
    ])]
    private Collection $reviews;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ContentShoppingCart::class, cascade: [
        'remove'
    ])]
    private Collection $contentShoppingCarts;



    public function __construct()
    {
        $this->dateAdded = new \DateTime();
        $this->images = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->contentShoppingCarts = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function PrePersist() {
        $this->slug = (new Slugify())->slugify($this->name);
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPriceExclVat(): ?string
    {
        return $this->priceExclVat;
    }

    public function setPriceExclVat(string $priceExclVat): self
    {
        $this->priceExclVat = $priceExclVat;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getDateAdded(): ?\DateTimeInterface
    {
        return $this->dateAdded;
    }

    public function setDateAdded(\DateTimeInterface $dateAdded): self
    {
        $this->dateAdded = $dateAdded;

        return $this;
    }

    public function getBrand(): ?Brand
    {
        return $this->brand;
    }

    public function setBrand(?Brand $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getPromotion(): ?Promotion
    {
        return $this->promotion;
    }

    public function setPromotion(?Promotion $promotion): self
    {
        $this->promotion = $promotion;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, Image>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setProduct($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getProduct() === $this) {
                $image->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): self
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setProduct($this);
        }

        return $this;
    }

    public function removeReview(Review $review): self
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getProduct() === $this) {
                $review->setProduct(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection<int, ContentShoppingCart>
     */
    public function getContentShoppingCarts(): Collection
    {
        return $this->contentShoppingCarts;
    }

    public function addContentShoppingCart(ContentShoppingCart $contentShoppingCart): self
    {
        if (!$this->contentShoppingCarts->contains($contentShoppingCart)) {
            $this->contentShoppingCarts->add($contentShoppingCart);
            $contentShoppingCart->setProduct($this);
        }

        return $this;
    }

    public function removeContentShoppingCart(ContentShoppingCart $contentShoppingCart): self
    {
        if ($this->contentShoppingCarts->removeElement($contentShoppingCart)) {
            // set the owning side to null (unless already changed)
            if ($contentShoppingCart->getProduct() === $this) {
                $contentShoppingCart->setProduct(null);
            }
        }

        return $this;
    }

    public function getTva(): ?string
    {
        return $this->tva;
    }

    public function setTva(string $tva): self
    {
        $this->tva = $tva;

        return $this;
    }
}
