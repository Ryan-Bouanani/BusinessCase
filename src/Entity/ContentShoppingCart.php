<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Repository\ContentShoppingCartRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContentShoppingCartRepository::class)]
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
class ContentShoppingCart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read:Basket:attributes'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['read:Basket:attributes'])]
    #[
         Assert\NotBlank([
            'message' => "Veuillez remplir tout les champs."
        ]),
        Assert\Positive
    ]
    private ?int $quantity = null;

    #[Groups(['read:Basket:attributes'])]
    #[ORM\Column(type: Types::DECIMAL, precision: 7, scale: 2)]
    #[
         Assert\NotBlank([
            'message' => "Veuillez remplir tout les champs."
        ]),
        Assert\PositiveOrZero,
        Assert\Range(
            min: 0,
            max: 99000.99,
            notInRangeMessage: 'Le prix TTC doit être plus grand que 0 et inferieur à 100 000',
        )
    ]
    private ?string $price = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 4, scale: 2)]
    #[
        Assert\NotBlank([
            'message' => "Veuillez remplir tout les champs."
        ]),
    ]
    private ?string $tva = null;

    #[ORM\ManyToOne(inversedBy: 'contentShoppingCarts')]
    #[ORM\JoinColumn(nullable: false)]
    #[
         Assert\NotBlank([
            'message' => "Veuillez remplir tout les champs."
        ]),
    ]
    private ?Basket $basket = null;

    #[Groups(['read:Basket:attributes'])]
    #[ORM\ManyToOne(inversedBy: 'contentShoppingCarts')]
    private ?Product $product = null;

    // #[ORM\JoinColumn(nullable: true)]


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

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


    public function getBasket(): ?Basket
    {
        return $this->basket;
    }

    public function setBasket(?Basket $basket): self
    {
        $this->basket = $basket;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }
}
