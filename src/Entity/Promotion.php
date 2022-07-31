<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\PromotionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PromotionRepository::class)]
class Promotion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[
        Assert\NotBlank([
            'message' => "Veuiller remplir tout les champs."
        ]),
        Assert\Length([
            'min' => 2,
            'max' => 255,
            'minMessage' => 'Veuiller entrer une promotion contenant au minimum {{ limit }} caractères',
            'maxMessage' => 'Veuiller entrer une promotion contenant au maximum {{ limit }} caractères',
        ]),
    ]
    private ?string $name = null;

    #[ORM\Column]
    #[
        Assert\NotBlank([
            'message' => "Veuiller remplir tout les champs."
        ]),
        Assert\Positive
    ]
    private ?int $percentage = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[
        Assert\NotBlank([
            'message' => "Veuiller remplir tout les champs."
        ]),
        Assert\Date([
            'message' => 'Veuiller entrer une date valide.',
        ])
    ]
    private ?\DateTimeInterface $expirationDate = null;

    #[ORM\OneToMany(mappedBy: 'promotion', targetEntity: Product::class)]
    private Collection $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPercentage(): ?int
    {
        return $this->percentage;
    }

    public function setPercentage(int $percentage): self
    {
        $this->percentage = $percentage;

        return $this;
    }

    public function getExpirationDate(): ?\DateTimeInterface
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(\DateTimeInterface $expirationDate): self
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setPromotion($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getPromotion() === $this) {
                $product->setPromotion(null);
            }
        }

        return $this;
    }
}
