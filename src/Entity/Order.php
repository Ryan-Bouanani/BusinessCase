<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\NbOrderAction;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\OrderRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
#[ApiResource(
    attributes: [
        "security" => "is_granted('ROLE_ADMIN')  or is_granted('ROLE_STATS')",
        "security_message" => "Accès refusé",
        'normalization_context' => ['groups' => ['read:Order:attributes']],
    ],
    collectionOperations: [
        'get',
        'getNbOrder' => [
            'method' => 'GET',
            'path' => 'stats/nbOrders',
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
    ],
    itemOperations: [
        'get'
    ],
)]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read:Order:attributes'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[
        Assert\NotBlank,
    ]
    private ?\DateTimeInterface $billingDate = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    #[
        Assert\NotBlank,
    ]
    private ?Status $status = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['read:Order:attributes'])]
    #[
        Assert\NotBlank,
    ]
    private ?Basket $basket = null;

    #[ORM\ManyToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[
        Assert\NotBlank,
    ]
    private ?MeanOfPayment $meanOfPayment = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getBasket(): ?Basket
    {
        return $this->basket;
    }

    public function setBasket(Basket $basket): self
    {
        $this->basket = $basket;

        return $this;
    }

    public function getMeanOfPayment(): ?MeanOfPayment
    {
        return $this->meanOfPayment;
    }

    public function setMeanOfPayment(MeanOfPayment $meanOfPayment): self
    {
        $this->meanOfPayment = $meanOfPayment;

        return $this;
    }
}
