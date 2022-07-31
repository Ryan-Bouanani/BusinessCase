<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\averagePriceBasketAction;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Controller\NbBasketAction;
use App\Repository\BasketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BasketRepository::class)]
#[ApiResource(
    
    attributes: [
        "security" => "is_granted('ROLE_ADMIN') or is_granted('ROLE_STATS')",
        "security_message" => "Accès refusé",
        'normalization_context' => ['groups' => ['read:Basket:attributes']],
        'denormalization_context' => ['groups' => ['read:Basket:attributes']],
    ],
    collectionOperations: [
        'get',
        // NB BASKET
        'getNbBasket' => [
            'method' => 'GET',
            'path' => 'stats/nbBaskets',
            'controller' => NbBasketAction::class,
            'read' => false,
            'pagination_enabled' => false,
            'openapi_context' => [
                'summary' => 'Recupère le nombre total de paniers',
                'parameters' => [],
                'responses' => [
                    '200' => [
                        'description' => 'Nombre de paniers',
                        'content' => [
                            'application/json' => [
                                'schema'=> [
                                    'type' => 'integer',
                                    'example' => 400
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ],
        // AVERAGE PRICE BASKET
        'averagePriceBasket' => [
            'method' => 'GET',
            'path' => 'statss/averagePriceBasket',
            'controller' => averagePriceBasketAction::class,
            'pagination_enabled' => false,
            'openapi_context' => [
                'summary' => 'Recupère le prix d\'un panier moyen',
                'parameters' => [],
                'responses' => [
                    '200' => [
                        'description' => 'Prix moyen d\'un panier',
                        'content' => [
                            'application/json' => [
                                'schema'=> [
                                    'type' => 'float',
                                    'example' => 43.5
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ],
    ],
    //modifier ce qu'il y'a dans la doc se termine par l'id
    itemOperations: [
        'get'
    ],
)]
class Basket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read:Basket:attributes'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['read:Basket:attributes'])]
    #[
        Assert\NotBlank,
    ]
    private ?\DateTimeInterface $dateCreated = null;

    #[ORM\OneToMany(mappedBy: 'basket', targetEntity: ContentShoppingCart::class)]
    #[Groups(['read:Basket:attributes'])]
    #[
        Assert\NotBlank,
    ]
    private Collection $contentShoppingCarts;

    #[ORM\ManyToOne(inversedBy: 'baskets')]
    #[Groups(['read:Basket:attributes'])]
    #[
        Assert\NotBlank,
    ]
    private ?Customer $customer = null;

    public function __construct()
    {
        $this->contentShoppingCarts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->dateCreated;
    }

    public function setDateCreated(\DateTimeInterface $dateCreated): self
    {
        $this->dateCreated = $dateCreated;

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
            $contentShoppingCart->setBasket($this);
        }

        return $this;
    }

    public function removeContentShoppingCart(ContentShoppingCart $contentShoppingCart): self
    {
        if ($this->contentShoppingCarts->removeElement($contentShoppingCart)) {
            // set the owning side to null (unless already changed)
            if ($contentShoppingCart->getBasket() === $this) {
                $contentShoppingCart->setBasket(null);
            }
        }

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }
}
