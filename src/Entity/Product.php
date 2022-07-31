<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ApiResource(
    collectionOperations: [
        'get',
    ],
    itemOperations: [
        'get',
    
    ],
)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read:Basket:attributes'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[
        Assert\NotBlank([
            'message' => "Veuiller remplir tout les champs."
        ]),
        Assert\Length([
            'min' => 2,
            'max'=> 255,
            'minMessage' => 'Veuiller entrer un produict contenant au minimum {{ limit }} caractères',
            'maxMessage' => 'Veuiller entrer un produict contenant au maximum {{ limit }} caractères',
        ]),
    ]

    #[Groups(['read:Basket:attributes'])]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[
        Assert\NotBlank([
            'message' => "Veuiller remplir tout les champs."
        ]),
        Assert\Length([
            'min' => 2,
            'max' => 500,
            'minMessage' => 'Veuiller entrer une description contenant au minimum {{ limit }} caractères',
            'maxMessage' => 'Veuiller entrer une description contenant au maximum {{ limit }} caractères',
        ]),
    ]

    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 7, scale: 2)]
    #[
        Assert\NotBlank([
            'message' => "Veuiller remplir tout les champs."
        ]),
        Assert\PositiveOrZero(),
        Assert\Range(
            min: 0,
            max: 99000.99,
            notInRangeMessage: 'Le prix TTC doit être plus grand que 0 et inferieur à 100 000',
        )
    ]
    private ?string $priceExclVat = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 7, scale: 2)]
    #[
        Assert\NotBlank([
            'message' => "Veuiller remplir tout les champs."
        ]),
        Assert\PositiveOrZero,
        Assert\Range(
            min: 0,
            max: 99000.99,
            notInRangeMessage: 'Le prix TTC doit être plus grand que 0 et inferieur à 100 000',
        )
    ]
    private ?string $priceVat = null;

    #[ORM\Column]
    #[
        Assert\NotBlank([
            'message' => "Veuiller remplir tout les champs."
        ]),
    ]
    private ?bool $active = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[
        Assert\NotBlank([
            'message' => "Veuiller remplir tout les champs."
        ]),
    ]
    private ?\DateTimeInterface $dateAdded = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    #[
        Assert\NotBlank([
            'message' => "Veuiller remplir tout les champs."
        ]),
    ]
    private ?Brand $brand = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Promotion $promotion = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    #[
        Assert\NotBlank([
            'message' => "Veuiller remplir tout les champs."
        ]),
    ]
    private ?Category $category = null;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: Image::class)]
    #[
        Assert\NotBlank([
            'message' => "Veuiller remplir tout les champs."
        ]),
    ]
    private Collection $images;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: Review::class)]
    private Collection $reviews;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ContentShoppingCart::class)]
    private Collection $contentShoppingCarts;


    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->contentShoppingCarts = new ArrayCollection();
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

    public function getPriceExclVat(): ?string
    {
        return $this->priceExclVat;
    }

    public function setPriceExclVat(string $priceExclVat): self
    {
        $this->priceExclVat = $priceExclVat;

        return $this;
    }

    public function getPriceVat(): ?string
    {
        return $this->priceVat;
    }

    public function setPriceVat(string $priceVat): self
    {
        $this->priceVat = $priceVat;

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
}
