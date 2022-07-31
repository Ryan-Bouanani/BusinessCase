<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\MeanOfPaymentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MeanOfPaymentRepository::class)]
class MeanOfPayment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[
        Assert\NotBlank,
        Assert\Length([
            'min' => 2,
            'max' => 255,
            'minMessage' => 'Veuiller entrer un moyen de paiement contenant au minimum {{ limit }} caractères',
            'maxMessage' => 'Veuiller entrer un moyen de paiement contenant au maximum {{ limit }} caractères',
        ]),
    ]
    private ?string $designation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(string $designation): self
    {
        $this->designation = $designation;

        return $this;
    }
}
