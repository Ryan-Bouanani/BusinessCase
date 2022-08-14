<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\StatusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: StatusRepository::class)]
#[ApiResource(
    attributes: [
        "security" => "is_granted('ROLE_ADMIN')  or is_granted('ROLE_STATS')",
        "security_message" => "Accès refusé",
    ],
    collectionOperations: [
        'get',
    ],
    itemOperations: [
        'get'
    ],
)]
class Status
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read:Order:attributes'])]
    #[
        Assert\NotBlank([
            'message' => "Veuiller remplir tout les champs."
        ]),
        Assert\Choice([
            'choices' => [
                'Expédié',
                'Echouer',
                'Annuller',
                'En attente',
                'En cours de preparation',
                'En cours d\'expédition',
            ],
            'message' => 'Choississer un status valide.',
        ]),
    ]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'status', targetEntity: Basket::class)]
    private Collection $baskets;


    public function __construct()
    {
        $this->orders = new ArrayCollection();
        $this->baskets = new ArrayCollection();
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

    /**
     * @return Collection<int, Basket>
     */
    public function getBaskets(): Collection
    {
        return $this->baskets;
    }

    public function addBasket(Basket $basket): self
    {
        if (!$this->baskets->contains($basket)) {
            $this->baskets->add($basket);
            $basket->setStatus($this);
        }

        return $this;
    }

    public function removeBasket(Basket $basket): self
    {
        if ($this->baskets->removeElement($basket)) {
            // set the owning side to null (unless already changed)
            if ($basket->getStatus() === $this) {
                $basket->setStatus(null);
            }
        }

        return $this;
    }
}
