<?php

namespace App\Entity;

use App\Repository\AddressRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
class Address
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column (nullable : true)]
    #[
        Assert\PositiveOrZero,
    ]
    private ?int $number = null;

    #[ORM\Column(length: 255)]
    #[
        Assert\NotBlank([
            'message' => "Veuiller remplir tout les champs."
        ]),
        Assert\Length([
            'min' => 2,
            'max' => 255,
            'minMessage' => 'Veuiller entrer un produit contenant au minimum {{ limit }} caractères',
            'maxMessage' => 'Veuiller entrer un produit contenant au maximum {{ limit }} caractères',
        ]),
    ]
    private ?string $street = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[
        Assert\Length([
            'max' => 255,
            'maxMessage' => 'Veuiller entrer une information contenant au maximum {{ limit }} caractères',
        ]),
    ]
    private ?string $line3 = null;

    #[ORM\Column(length: 15)]
    #[
        Assert\NotBlank([
            'message' => "Veuiller remplir tout les champs."
        ]),
        Assert\Length([
            'min' => 2,
            'max' => 5,
            'minMessage' => 'Veuiller entrer un code postale contenant au minimum {{ limit }} caractères',
            'maxMessage' => 'Veuiller entrer un code postale contenant au maximum {{ limit }} caractères',
        ]),
    ]
    private ?string $postalCode = null;

    #[ORM\Column(length: 255)]
    #[
         Assert\NotBlank([
            'message' => "Veuiller remplir tout les champs."
        ]),
        Assert\Length([
            'min' => 2,
            'max' => 255,
            'minMessage' => 'Veuiller entrer une ville contenant au minimum {{ limit }} caractères',
            'maxMessage' => 'Veuiller entrer une ville contenant au maximum {{ limit }} caractères',
        ]),
    ]
    private ?string $city = null;


    #[ORM\ManyToOne(inversedBy: 'addresses')]
    #[ORM\JoinColumn(nullable: false)]
    #[
         Assert\NotBlank([
            'message' => "Veuiller remplir tout les champs."
        ]),
    ]
    private ?Customer $customer = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(? int $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getLine3(): ?string
    {
        return $this->line3;
    }

    public function setLine3(string $line3): self
    {
        $this->line3 = $line3;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

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
