<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\Back\Stats\AveragePriceBasketAction as StatsAveragePriceBasketAction;
use App\Controller\Back\Stats\VisiteConversionBasketPercentage;
use App\Controller\Back\Stats\BestSellingProductAction;
use App\Controller\Back\Stats\NbBasketAction;
use App\Controller\Back\Stats\NbOrderAction;
use App\Controller\Back\Stats\OrderConversionPercentageAction;
use App\Controller\Back\Stats\PercentageAbandonedBasketAction as StatsPercentageAbandonedBasketAction;
use App\Controller\Back\Stats\RecurrenceOrderCustomerAction;
use App\Controller\Back\Stats\TurnoverAction;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\BasketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BasketRepository::class)]
#[ApiResource(
    
    attributes: [
        "security" => "is_granted('ROLE_STATS')",
        "security_message" => "Accès refusé",
        'normalization_context' => ['groups' => ['read:Basket:attributes']],
        'normalization_context' => ['groups' => ['read:Order:attributes']],
    ],
    collectionOperations: [
        // 'get',
        // NB ORDERS
        'getNbOrder' => [
            'method' => 'GET',
            'path' => 'stats/nbOrder',
            'controller' => NbOrderAction::class,
            'read' => false,
            'pagination_enabled' => false,
            'openapi_context' => [
                'summary' => 'Recupère le nombre total de commandes',
                'parameters' => [],
                'responses' => [
                    '200' => [
                        'description' => 'Nombre de commandes',
                        'content' => [
                            'application/json' => [
                                'schema'=> [
                                    'type' => 'integer',
                                    'example' => 500
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ],
        // TURNOVER
        'getTurnover' => [
            'method' => 'GET',
            'path' => 'stats/turnover',
            'controller' => TurnoverAction::class,
            'read' => false,
            'pagination_enabled' => false,
            'openapi_context' => [
                'summary' => 'Recupère le chiffre d\'affaire (total des produits vendues)',
                'parameters' => [],
                'responses' => [
                    '200' => [
                        'description' => 'Chiffre d\'affaire',
                        'content' => [
                            'application/json' => [
                                'schema'=> [
                                    'type' => 'float',
                                    'example' => 1252.25
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ],
        // BEST SELLING PRODUCTS
        'getBestSellingProduct' => [
            'method' => 'GET',
            'path' => 'stats/bestSellingProduct',
            'controller' => BestSellingProductAction::class,
            'read' => false,
            'pagination_enabled' => false,
            'openapi_context' => [
                'summary' => 'Recupère les produits les plus vendues',
                'parameters' => [],
                'responses' => [
                    '200' => [
                        'description' => 'Produits les plus vendues',
                        'content' => [
                            'application/json' => [
                                'schema'=> [
                                    'type' => 'float',
                                    'example' => 1252.25
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ],
         // AVERAGE PRICE BASKET
         'getAveragePriceBasket' => [
            'method' => 'GET',
            'path' => 'stats/averagePriceBasket',
            'controller' => StatsAveragePriceBasketAction::class,
            'read' => false,
            'pagination_enabled' => false,
            'openapi_context' => [
                'summary' => 'Recupère le prix d\'un panier moyen',
                'parameters' => [],
                'responses' => [
                    '200' => [
                        'description' => 'Prix d\'un panier moyen',
                        'content' => [
                            'application/json' => [
                                'schema'=> [
                                    'type' => 'float',
                                    'example' => 35.50
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        // PERCENTAGE ABANDONED BASKET
        'getPercentageAbandonedBasket' => [
            'method' => 'GET',
            'path' => 'stats/percentageAbandonedBasket',
            'controller' => StatsPercentageAbandonedBasketAction::class,
            'read' => false,
            'pagination_enabled' => false,
            'openapi_context' => [
                'summary' => 'Recupère le pourcentage de paniers abandonnés',
                'parameters' => [],
                'responses' => [
                    '200' => [
                        'description' => 'Pourcentage de paniers abandonnés',
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
        // PERCENTAGE CONVERSION ORDER
        'percentageBasketsConvertedIntoOrders' => [
            'method' => 'GET',
            'path' => 'stats/percentageBasketsConvertedIntoOrders',
            'controller' => OrderConversionPercentageAction::class,
            'read' => false,
            'pagination_enabled' => false,
            'openapi_context' => [
                'summary' => 'Recupère le pourcentage de paniers transformés en commandes',
                'parameters' => [],
                'responses' => [
                    '200' => [
                        'description' => 'Pourcentage de paniers transformés en commandes',
                        'content' => [
                            'application/json' => [
                                'schema'=> [
                                    'type' => 'float',
                                    'example' => 10.5
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ],
        // PERCENTAGE CONVERSION ORDER
        'getVisiteConversionBasketPercentage' => [
            'method' => 'GET',
            'path' => 'stats/visiteConversionBasketPercentage',
            'controller' => VisiteConversionBasketPercentage::class,
            'read' => false,
            'pagination_enabled' => false,
            'openapi_context' => [
                'summary' => 'Recupère le pourcentage de visites converti en paniers',
                'parameters' => [],
                'responses' => [
                    '200' => [
                        'description' => 'Pourcentage de visites converti en paniers',
                        'content' => [
                            'application/json' => [
                                'schema'=> [
                                    'type' => 'float',
                                    'example' => 10.5
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ],
        // NB BASKET
        'getNbBasket' => [
            'method' => 'GET',
            'path' => 'stats/nbBasket',
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
            ]
        ],     
        // PERCENTAGE RECURRENCE ORDER CUSTOMER
        'getPercentageRecurrenceOrderCustomer' => [
            'method' => 'GET',
            'path' => 'stats/percentageRecurrenceOrderCustomer',
            'controller' => RecurrenceOrderCustomerAction::class,
            'read' => false,
            'pagination_enabled' => false,
            'openapi_context' => [
                'summary' => 'Recupère le pourcentage de récurrence de commandes clients ',
                'parameters' => [],
                'responses' => [
                    '200' => [
                        'description' => 'Pourcentage de récurrence de commandes clients',
                        'content' => [
                            'application/json' => [
                                'schema'=> [
                                    'type' => 'float',
                                    'example' => 17.2
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],     
    ],
    itemOperations: [
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
        Assert\NotBlank([
            'message' => "Veuillez remplir tout les champs."
        ]),
    ]
    private ?\DateTimeInterface $dateCreated = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['read:Basket:attributes'])]
    private ?\DateTimeInterface $billingDate = null;

    #[ORM\OneToMany(mappedBy: 'basket', targetEntity: ContentShoppingCart::class, cascade: [
        'persist',
        'remove'
    ])]
    #[Groups(['read:Basket:attributes'])]
    #[
          Assert\NotBlank([
            'message' => "Veuillez remplir tout les champs."
        ]),
    ]
    private Collection $contentShoppingCarts;

    #[ORM\ManyToOne(inversedBy: 'baskets')]
    #[Groups(['read:Basket:attributes'])]
    private ?Customer $customer = null;

    #[ORM\ManyToOne(inversedBy: 'baskets')]
    #[
        Assert\NotBlank([
            'message' => "Veuillez remplir tout les champs."
        ]),
    ]
    private ?MeanOfPayment $meanOfPayment = null;

    #[ORM\ManyToOne(inversedBy: 'baskets')]
    private ?Status $status = null;

    #[ORM\ManyToOne(inversedBy: 'baskets')]
    private ?Address $address = null;




    public function __construct()
    {
        $this->dateCreated = new \DateTime();
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

    public function getBillingDate(): ?\DateTimeInterface
    {
        return $this->billingDate;
    }

    public function setBillingDate(\DateTimeInterface $billingDate): self
    {
        $this->billingDate = $billingDate;

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

    public function getMeanOfPayment(): ?MeanOfPayment
    {
        return $this->meanOfPayment;
    }

    public function setMeanOfPayment(?MeanOfPayment $meanOfPayment): self
    {
        $this->meanOfPayment = $meanOfPayment;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): self
    {
        $this->address = $address;

        return $this;
    }
   
}
