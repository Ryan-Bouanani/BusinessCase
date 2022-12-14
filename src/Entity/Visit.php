<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\Back\Stats\NbVisitAction;
use App\Repository\VisitRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VisitRepository::class)]
#[ApiResource(

    attributes: [
        "security" => "is_granted('ROLE_STATS')",
        "security_message" => "Accès refusé",
    ],
    collectionOperations: [
        // NB BASKET
        'getNbVisit' => [
            'method' => 'GET',
            'path' => 'stats/nbVisit',
            'controller' => NbVisitAction::class,
            'read' => false,
            'pagination_enabled' => false,
            'openapi_context' => [
                'summary' => 'Recupère le nombre total de visites',
                'parameters' => [],
                'responses' => [
                    '200' => [
                        'description' => 'Nombre de visites',
                        'content' => [
                            'application/json' => [
                                'schema'=> [
                                    'type' => 'integer',
                                    'example' => 550
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
class Visit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $visitAt = null;

    public function __construct()
    {
        $this->visitAt = new \DateTime();   
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVisitAt(): ?\DateTimeInterface
    {
        return $this->visitAt;
    }

    public function setVisitAt(\DateTimeInterface $visitAt): self
    {
        $this->visitAt = $visitAt;

        return $this;
    }
}

