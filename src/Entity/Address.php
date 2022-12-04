<?php

namespace App\Entity;

use App\Repository\AddressRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
class Address
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
           'minMessage' => 'Veuiller entrer un prenom contenant au minimum {{ limit }} caractères',
           'maxMessage' => 'Veuiller entrer un prenom contenant au maximum {{ limit }} caractères',
       ]),
   ]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[
        Assert\NotBlank([
           'message' => "Veuiller remplir tout les champs."
       ]),
       Assert\Length([
           'min' => 2,
           'max' => 255,
           'minMessage' => 'Veuiller entrer un nom contenant au minimum {{ limit }} caractères',
           'maxMessage' => 'Veuiller entrer un nom contenant au maximum {{ limit }} caractères',
       ]),
   ]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 15)]
    #[
        Assert\NotBlank([
            'message' => "Veuiller remplir tout les champs."
        ]),
        Assert\Length([
            'min' => 2,
            'max' => 15,
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


        #[ORM\Column(length: 255)]
        #[
            Assert\NotBlank([
                'message' => "Veuiller remplir tout les champs."
            ]),
            Assert\Length([
                'min' => 3,
                'max' => 255,
                'minMessage' => 'Veuiller entrer un pays contenant au minimum {{ limit }} caractères',
                'maxMessage' => 'Veuiller entrer un pays contenant au maximum {{ limit }} caractères',
            ]),
        ]
        private ?string $country = null;

    #[ORM\Column(length: 255)]
    #[
        Assert\NotBlank([
            'message' => "Veuiller remplir tout les champs."
        ]),
        Assert\Length([
            'min' => 2,
            'max' => 255,
            'minMessage' => 'Veuiller entrer une rue contenant au minimum {{ limit }} caractères',
            'maxMessage' => 'Veuiller entrer une rue contenant au maximum {{ limit }} caractères',
        ]),
    ]
    private ?string $line1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[
        Assert\Length([
            'max' => 255,
            'maxMessage' => 'Veuiller entrer une information contenant au maximum {{ limit }} caractères',
        ]),
    ]
    private ?string $line2 = null;

    #[ORM\OneToMany(mappedBy: 'address', targetEntity: Basket::class)]
    private Collection $baskets;

    #[ORM\OneToMany(mappedBy: 'address', targetEntity: Customer::class)]
    private Collection $customers;


    public function __construct()
    {
        $this->baskets = new ArrayCollection();
        $this->customers = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getLine1(): ?string
    {
        return $this->line1;
    }

    public function setLine1(string $line1): self
    {
        $this->line1 = $line1;

        return $this;
    }

    public function getLine2(): ?string
    {
        return $this->line2;
    }

    public function setLine2(?string $line2): self
    {
        $this->line2 = $line2;

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
            $basket->setAddress($this);
        }

        return $this;
    }

    public function removeBasket(Basket $basket): self
    {
        if ($this->baskets->removeElement($basket)) {
            // set the owning side to null (unless already changed)
            if ($basket->getAddress() === $this) {
                $basket->setAddress(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Customer>
     */
    public function getCustomers(): Collection
    {
        return $this->customers;
    }

    public function addCustomer(Customer $customer): self
    {
        if (!$this->customers->contains($customer)) {
            $this->customers->add($customer);
            $customer->setAddress($this);
        }

        return $this;
    }

    public function removeCustomer(Customer $customer): self
    {
        if ($this->customers->removeElement($customer)) {
            // set the owning side to null (unless already changed)
            if ($customer->getAddress() === $this) {
                $customer->setAddress(null);
            }
        }

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }
}
